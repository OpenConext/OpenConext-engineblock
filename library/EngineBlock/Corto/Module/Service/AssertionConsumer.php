<?php

/**
 * Copyright 2010 SURFnet B.V.
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
use OpenConext\EngineBlock\Metadata\Factory\Factory\ServiceProviderFactory;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlock\Service\ProcessingStateHelperInterface;
use OpenConext\EngineBlock\Stepup\StepupGatewayCallOutHelper;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationState;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use SAML2\Constants;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

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
    /**
     * @var ServiceProviderFactory
     */
    private $_serviceProviderFactory;

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        EngineBlock_Corto_XmlToArray $xmlConverter,
        Session $session,
        ProcessingStateHelperInterface $processingStateHelper,
        StepupGatewayCallOutHelper $stepupGatewayCallOutHelper,
        ServiceProviderFactory $serviceProviderFactory
    )
    {
        $this->_server = $server;
        $this->_xmlConverter = $xmlConverter;
        $this->_session = $session;
        $this->_processingStateHelper = $processingStateHelper;
        $this->_stepupGatewayCallOutHelper = $stepupGatewayCallOutHelper;
        $this->_serviceProviderFactory = $serviceProviderFactory;
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

        if ($receivedResponse->isTransparentErrorResponse()) {
            $log->info('Response contains an error response status code, SP is configured with transparent_authn_context.');
            $response = $this->_server->createTransparentErrorResponse($receivedRequest, $receivedResponse);
            $log->info('Sending AuthnFailed response back to SP');
            $this->_server->sendResponseToRequestIssuer($receivedRequest, $response);
            return;
        }

        // Test if we should return a no passive status response back to the SP
        if (in_array(Constants::STATUS_NO_PASSIVE, $receivedResponse->getStatus())) {
            $log->info('Response contains NoPassive status code: responding with NoPassive status to SP');
            $response = $this->_server->createNoPassiveResponse($receivedRequest);
            $this->_server->sendResponseToRequestIssuer($receivedRequest, $response);
            return;
        }

        $this->_server->checkResponseSignatureMethods($receivedResponse);

        if ($receivedRequest->isDebugRequest()) {
            $sp = $this->getEngineSpRole($this->_server);
        } else {
            $issuer = $receivedRequest->getIssuer() ? $receivedRequest->getIssuer()->getValue() : '';
            $sp = $this->_server->getRepository()->fetchServiceProviderByEntityId($issuer);
        }

        // Verify the SP requester chain.
        EngineBlock_SamlHelper::getSpRequesterChain(
            $sp,
            $receivedRequest,
            $this->_server->getRepository()
        );

        // Flush log if SP or IdP has additional logging enabled
        $issuer = $receivedResponse->getIssuer() ? $receivedResponse->getIssuer()->getValue() : '';
        $idp = $this->_server->getRepository()->fetchIdentityProviderByEntityId($issuer);

        // Set SSO session cookie if 'feature_enable_sso_notification' is enabled and the response is successful
        if ($application->getDiContainer()->getFeatureConfiguration()->isEnabled("eb.enable_sso_session_cookie")) {
            if (in_array(Constants::STATUS_SUCCESS, $receivedResponse->getStatus())) {
                $application->getDiContainer()->getSsoSessionService()->setSsoSessionCookie(
                    $application->getDiContainer()->getSymfonyRequest()->cookies,
                    $idp->entityId
                );
            }
        }

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

        // When dealing with an SP that acts as a trusted proxy, we should use the proxying SP and not the proxy itself.
        if ($sp->getCoins()->isTrustedProxy()) {
            // Overwrite the trusted proxy SP instance with that of the SP that uses the trusted proxy.
            $sp = $this->_server->findOriginalServiceProvider($receivedRequest, $log);
        }

        $pdpLoas = $receivedResponse->getPdpRequestedLoas();
        $loaRepository = $application->getDiContainer()->getLoaRepository();
        $authnRequestLoas = $receivedRequest->getStepupObligations($loaRepository->getStepUpLoas());
        // Goto consent if no Stepup authentication is needed
        if (!$this->_stepupGatewayCallOutHelper->shouldUseStepup($idp, $sp, $authnRequestLoas, $pdpLoas)) {
            $this->_server->sendConsentAuthenticationRequest($receivedResponse, $receivedRequest, $currentProcessStep->getRole(), $this->getAuthenticationState());
            return;
        }

        $log->info('Handle Stepup authentication callout');

        // Add Stepup authentication step
        $currentProcessStep = $this->_processingStateHelper->addStep(
            $receivedRequest->getId(),
            ProcessingStateHelperInterface::STEP_STEPUP,
            $application->getDiContainer()->getStepupIdentityProvider($this->_server),
            $receivedResponse
        );

        // Get mapped AuthnClassRef and get NameId
        $nameId = clone $receivedResponse->getNameId();
        $authnClassRef = $this->_stepupGatewayCallOutHelper->getStepupLoa($idp, $sp, $authnRequestLoas, $pdpLoas);

        $this->_server->sendStepupAuthenticationRequest(
            $receivedRequest,
            $currentProcessStep->getRole(),
            $authnClassRef,
            $nameId,
            $sp->getCoins()->isStepupForceAuthn(),
            $receivedResponse->getAssertions()[0]
        );
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
     */
    protected function getEngineSpRole(EngineBlock_Corto_ProxyServer $proxyServer)
    {
        $keyId = $proxyServer->getKeyId();
        if (!$keyId) {
            $keyId = KeyPairFactory::DEFAULT_KEY_PAIR_IDENTIFIER;
        }

        $serviceProvider = $this->_serviceProviderFactory->createEngineBlockEntityFrom($keyId);
        return ServiceProvider::fromServiceProviderEntity($serviceProvider);
    }
}
