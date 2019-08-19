<?php

/**
 * Copyright 2014 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Service\ProcessingStateHelperInterface;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationState;
use OpenConext\EngineBlockBundle\Stepup\StepupGatewayCallOutHelper;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class EngineBlock_Corto_Module_Service_AssertionConsumer implements EngineBlock_Corto_Module_Service_ServiceInterface
{
    /**
     * @var EngineBlock_Corto_ProxyServer
     */
    private $_server;

    /**
     * @var EngineBlock_Corto_XmlToArray
     */
    private $_xmlConverter;

    /**
     * @var Session
     */
    private $_session;

    /**
     * @var ProcessingStateHelperInterface
     */
    private $_processingStateHelper;
    /**
     * @var StepupGatewayCallOutHelper
     */
    private $_stepupGatewayCallOutHelper;

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        EngineBlock_Corto_XmlToArray $xmlConverter,
        Session $session,
        ProcessingStateHelperInterface $processingStateHelper,
        StepupGatewayCallOutHelper $stepupGatewayCallOutHelper
    ) {
        $this->_server = $server;
        $this->_xmlConverter = $xmlConverter;
        $this->_session = $session;
        $this->_processingStateHelper = $processingStateHelper;
        $this->_stepupGatewayCallOutHelper = $stepupGatewayCallOutHelper;
    }

    /**
     * @param $serviceName
     * @param Request $httpRequest
     */
    public function serve($serviceName, Request $httpRequest)
    {
        $serviceEntityId = $this->_server->getUrl('spMetadataService');
        $expectedDestination = $this->_server->getUrl('assertionConsumerService');

        $receivedResponse = $this->_server->getBindingsModule()->receiveResponse($serviceEntityId, $expectedDestination);
        $receivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse);

        $application = EngineBlock_ApplicationSingleton::getInstance();
        $log = $application->getLogInstance();

        $this->_server->checkResponseSignatureMethods($receivedResponse);

        $sp = $this->_server->getRepository()->fetchServiceProviderByEntityId($receivedRequest->getIssuer());

        // Verify the SP requester chain.
        EngineBlock_SamlHelper::getSpRequesterChain(
            $sp,
            $receivedRequest,
            $this->_server->getRepository()
        );

        // Flush log if SP or IdP has additional logging enabled
        $idp = $this->_server->getRepository()->fetchIdentityProviderByEntityId($receivedResponse->getIssuer());

        if (EngineBlock_SamlHelper::doRemoteEntitiesRequireAdditionalLogging(array($sp, $idp))) {
            $application->flushLog('Activated additional logging for the SP or IdP');
            $log->info('Raw HTTP request', array('http_request' => (string)$application->getHttpRequest()));
        }

        if ($receivedRequest->isDebugRequest()) {
            $_SESSION['debugIdpResponse'] = $receivedResponse;
            $requestId = $receivedResponse->getInResponseTo();

            // Authentication state needs to be registered here as the debug flow differs from the regular flow,
            // yet the procedures for both are completed when consuming the assertion in the ServiceProviderController
            $identityProvider = new Entity(new EntityId($idp->entityId), EntityType::IdP());
            $authenticationState = $this->getAuthenticationState();
            $authenticationState->authenticatedAt($requestId, $identityProvider);

            $this->_server->redirect(
                $this->_server->getUrl('debugSingleSignOnService'),
                'Show original Response from IDP'
            );
            return;
        }

        if ($receivedRequest->getKeyId()) {
            $this->_server->setKeyId($receivedRequest->getKeyId());
        }

        // Keep track of what IDP was used for this SP. This way the user does
        // not have to go trough the WAYF again when logging into this service
        // or another service.
        EngineBlock_Corto_Model_Response_Cache::rememberIdp($receivedRequest, $receivedResponse);

        $this->_server->filterInputAssertionAttributes($receivedResponse, $receivedRequest);

        // Add the consent step
        $currentProcessStep = $this->_processingStateHelper->addStep(
            $receivedRequest->getId(),
            ProcessingStateHelperInterface::STEP_CONSENT,
            $this->getEngineSpRole($this->_server),
            $receivedResponse
        );

        // Goto consent if no Stepup authentication is needed
        if (!$this->_stepupGatewayCallOutHelper->shouldUseStepup($idp, $sp)) {
            $this->_server->sendConsentAuthenticationRequest($receivedResponse, $receivedRequest, $currentProcessStep->getRole(), $this->getAuthenticationState());
            return;
        }

        $log->info('Handle Stepup authentication callout', array('key_id' => $receivedRequest->getId()));

        // Add Stepup authentication step
        $currentProcessStep = $this->_processingStateHelper->addStep(
            $receivedRequest->getId(),
            ProcessingStateHelperInterface::STEP_STEPUP,
            $application->getDiContainer()->getStepupIdentityProvider($this->_server),
            $receivedResponse
        );

        // Get mapped AuthnClassRef and get NameId
        $nameId = clone $receivedResponse->getNameId();
        $authnClassRef = $this->_stepupGatewayCallOutHelper->getStepupLoa($idp, $sp);

        $this->_server->sendStepupAuthenticationRequest($receivedRequest, $currentProcessStep->getRole(), $authnClassRef, $nameId);
    }

    /**
     * @return AuthenticationState
     */
    private function getAuthenticationState()
    {
        return $this->_session->get('authentication_state');
    }

    /**
     * @param EngineBlock_Corto_ProxyServer $proxyServer
     * @return ServiceProvider
     * @throws EngineBlock_Corto_ProxyServer_Exception
     * @throws EngineBlock_Exception
     */
    protected function getEngineSpRole(EngineBlock_Corto_ProxyServer $proxyServer)
    {
        $spEntityId = $proxyServer->getUrl('spMetadataService');
        $engineServiceProvider = $proxyServer->getRepository()->findServiceProviderByEntityId($spEntityId);
        if (!$engineServiceProvider) {
            throw new EngineBlock_Exception(
                sprintf(
                    "Unable to find EngineBlock configured as Service Provider. No '%s' in repository!",
                    $spEntityId
                )
            );
        }

        return $engineServiceProvider;
    }
}
