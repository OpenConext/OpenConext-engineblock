<?php
/**
 * Copyright 2019 SURFnet B.V.
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

use OpenConext\EngineBlock\Service\ProcessingStateHelperInterface;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationState;
use OpenConext\EngineBlockBundle\Sfo\SfoGatewayCallOutHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class EngineBlock_Corto_Module_Service_SfoAssertionConsumer implements EngineBlock_Corto_Module_Service_ServiceInterface
{
    /**
     * @var EngineBlock_Corto_ProxyServer
     */
    private $_server;

    /**
     * @var ProcessingStateHelperInterface
     */
    private $_processingStateHelper;

    /**
     * @var Session
     */
    private $_session;

    /**
     * @var SfoGatewayCallOutHelper
     */
    private $_sfoGatewayCallOutHelper;

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        Session $session,
        ProcessingStateHelperInterface $processingStateHelper,
        SfoGatewayCallOutHelper $sfoGatewayCallOutHelper
    ) {
        $this->_server = $server;
        $this->_session = $session;
        $this->_processingStateHelper = $processingStateHelper;
        $this->_sfoGatewayCallOutHelper = $sfoGatewayCallOutHelper;
    }

    /**
     * @param $serviceName
     * @param Request $httpRequest
     */
    public function serve($serviceName, Request $httpRequest)
    {
        $serviceEntityId = $this->_server->getUrl('sfoMetadataService');
        $expectedDestination = $this->_server->getUrl('sfoAssertionConsumerService');

        $application = EngineBlock_ApplicationSingleton::getInstance();
        $log = $application->getLogInstance();

        $checkResponseSignature = true;
        try {
            $receivedResponse = $this->_server->getBindingsModule()->receiveResponse($serviceEntityId, $expectedDestination);
            $receivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse);
        } catch (EngineBlock_Corto_Exception_ReceivedErrorStatusCode $e) {

            // Handle exceptions
            // - only continue if the loa level is not met but no sfo authentication is allowed
            $this->handleInvalidGatewayResponse($e, $log);

            $receivedResponse = $e->getResponse();
            $receivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse);

            $checkResponseSignature = false; // error responses from gateway are not signed

            // set response to loa1
            $processStep = $this->_processingStateHelper->getStepByRequestId($receivedRequest->getId(), ProcessingStateHelperInterface::STEP_SFO);
            $processStep->getResponse()->getAssertion()->setAuthnContextClassRef($this->_sfoGatewayCallOutHelper->getSfoLoa1());
            $this->_processingStateHelper->updateStepResponseByRequestId($receivedRequest->getId(), ProcessingStateHelperInterface::STEP_SFO, $processStep->getResponse());
        }

        if ($checkResponseSignature) {
            $this->_server->checkResponseSignatureMethods($receivedResponse);
        }

        $sp  = $this->_server->getRepository()->fetchServiceProviderByEntityId($receivedRequest->getIssuer());

        // Verify the SP requester chain.
        EngineBlock_SamlHelper::getSpRequesterChain(
            $sp,
            $receivedRequest,
            $this->_server->getRepository()
        );

        $log->info('Handled SFO callout successfully', array('key_id' => $receivedRequest->getId()));

        // Get active request
        $processStep = $this->_processingStateHelper->getStepByRequestId($receivedRequest->getId(), ProcessingStateHelperInterface::STEP_SFO);
        $receivedResponse = $processStep->getResponse();

        $nextProcessStep = $this->_processingStateHelper->getStepByRequestId(
            $receivedRequest->getId(),
            ProcessingStateHelperInterface::STEP_CONSENT
        );

        $this->_server->sendConsentAuthenticationRequest($receivedResponse, $receivedRequest, $nextProcessStep->getRole(), $this->getAuthenticationState());

        return;
    }

    /**
     * @return AuthenticationState
     */
    private function getAuthenticationState()
    {
        return $this->_session->get('authentication_state');
    }

    /**
     * @param EngineBlock_Corto_Exception_ReceivedErrorStatusCode $e
     * @throws EngineBlock_Corto_Exception_InvalidSfoCalloutResponse
     * @throws EngineBlock_Corto_Exception_InvalidSfoLoaLevel
     * @throws EngineBlock_Corto_Exception_UserCancelledSfoCallout
     */
    private function handleInvalidGatewayResponse(EngineBlock_Corto_Exception_ReceivedErrorStatusCode $e, LoggerInterface $log)
    {
        $receivedResponse = $e->getResponse();
        $receivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse);

        switch (true) {
            case ($e->getFeedbackStatusCode() === 'Responder/AuthnFailed' && $e->getFeedbackStatusMessage() === 'Authentication cancelled by user'):
                // user cancelled
                $log->info('User cancelled SFO callout', array('key_id' => $receivedRequest->getId(), 'result' => $e->getFeedbackInfo()));

                throw new EngineBlock_Corto_Exception_UserCancelledSfoCallout(
                    'User cancelled SFO callout'
                );

            case ($e->getFeedbackStatusCode() == 'Responder/NoAuthnContext' && $e->getFeedbackStatusMessage() === '(No message provided)'):
                // invalid loa level
                // should continue if no valid token is allowed

                $log->warning('Unmet loa level for SFO callout, trying allow no token', array('key_id' => $receivedRequest->getId(), 'result' => $e->getFeedbackInfo()));

                // check if no token allowed
                $processStep = $this->_processingStateHelper->getStepByRequestId($receivedRequest->getId(), ProcessingStateHelperInterface::STEP_SFO);
                $originalReceivedResponse = $processStep->getResponse();
                $originalReceivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse);

                $idp = $this->_server->getRepository()->fetchIdentityProviderByEntityId($originalReceivedResponse->getIssuer());
                $sp = $this->_server->getRepository()->fetchServiceProviderByEntityId($originalReceivedRequest->getIssuer());

                if ($this->_sfoGatewayCallOutHelper->allowNoToken($idp, $sp)) {

                    $log->warning('Allow no token allowed from sp/idp configuration, continuing', array('key_id' => $receivedRequest->getId(), 'result' => $e->getFeedbackInfo()));
                    return;
                }

                throw new EngineBlock_Corto_Exception_InvalidSfoLoaLevel(
                    'Invalid loa level encountered during SFO callout'
                );
        }

        $log->warning('Invalid status returned from SFO gateway', array('key_id' => $receivedRequest->getId(), 'result' => $e->getFeedbackInfo()));

        throw new EngineBlock_Corto_Exception_InvalidSfoCalloutResponse(
            sprintf(
                "Invalid status received from sfo gateway: '%s' '%s' ",
                $e->getFeedbackStatusCode(),
                $e->getFeedbackStatusMessage()
            )
        );
    }
}
