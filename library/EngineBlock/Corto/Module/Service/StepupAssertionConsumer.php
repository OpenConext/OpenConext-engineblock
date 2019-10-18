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

use OpenConext\EngineBlock\Service\ProcessingStateHelperInterface;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationState;
use OpenConext\EngineBlock\Stepup\StepupGatewayCallOutHelper;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class EngineBlock_Corto_Module_Service_StepupAssertionConsumer implements EngineBlock_Corto_Module_Service_ServiceInterface
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
     * @var StepupGatewayCallOutHelper
     */
    private $_stepupGatewayCallOutHelper;

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        Session $session,
        ProcessingStateHelperInterface $processingStateHelper,
        StepupGatewayCallOutHelper $stepupGatewayCallOutHelper
    ) {
        $this->_server = $server;
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
        $serviceEntityId = $this->_server->getUrl('stepupMetadataService');
        $expectedDestination = $this->_server->getUrl('stepupAssertionConsumerService');

        $application = EngineBlock_ApplicationSingleton::getInstance();
        $log = $application->getLogInstance();

        $checkResponseSignature = true;
        try {
            $receivedResponse = $this->_server->getBindingsModule()->receiveResponse($serviceEntityId, $expectedDestination);
            $receivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse);

            // Update the AuthnContextClassRef to the loa returned
            $mappedLoa = $this->_stepupGatewayCallOutHelper->getEbLoa($receivedResponse->getAssertion()->getAuthnContextClassRef());
            $this->updateProcessingStateLoa($receivedRequest, $receivedResponse, $mappedLoa);

            $log->warning('After Stepup authentication update received LoA', array('key_id' => $receivedRequest->getId(), 'result' => $mappedLoa));
        } catch (EngineBlock_Corto_Exception_ReceivedErrorStatusCode $e) {

            // The user is allowed to continue upon subcode: NoAuthnContext when the SP is configured with the coin: coin:stepup:allow_no_token == true
            // See: https://www.pivotaltracker.com/story/show/166729912
            $this->handleInvalidGatewayResponse($e, $log);

            $receivedResponse = $e->getResponse();
            $receivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse);

            $checkResponseSignature = false; // error responses from gateway are not signed

            // Update the AuthnContextClassRef to LoA 1
            $mappedLoa = $this->_stepupGatewayCallOutHelper->getStepupLoa1();
            $this->updateProcessingStateLoa($receivedRequest, $receivedResponse, $mappedLoa);
            $log->warning('After failed Stepup authentication set LoA to Loa1', array('key_id' => $receivedRequest->getId(), 'result' => $mappedLoa));
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

        $log->info('Handled Stepup authentication callout successfully', array('key_id' => $receivedRequest->getId()));

        // Get active request
        $processStep = $this->_processingStateHelper->getStepByRequestId($receivedRequest->getId(), ProcessingStateHelperInterface::STEP_STEPUP);
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
     * @throws EngineBlock_Corto_Exception_InvalidStepupCalloutResponse
     * @throws EngineBlock_Corto_Exception_InvalidStepupLoaLevel
     * @throws EngineBlock_Corto_Exception_UserCancelledStepupCallout
     */
    private function handleInvalidGatewayResponse(EngineBlock_Corto_Exception_ReceivedErrorStatusCode $e, LoggerInterface $log)
    {
        $receivedResponse = $e->getResponse();
        $receivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse);

        switch (true) {
            case ($e->getFeedbackStatusCode() === 'Responder/AuthnFailed' && $e->getFeedbackStatusMessage() === 'Authentication cancelled by user'):
                // user cancelled
                $log->info('User cancelled Stepup authentication callout', array('key_id' => $receivedRequest->getId(), 'result' => $e->getFeedbackInfo()));

                throw new EngineBlock_Corto_Exception_UserCancelledStepupCallout(
                    'User cancelled Stepup authentication callout',
                    $e
                );

            case ($e->getFeedbackStatusCode() == 'Responder/NoAuthnContext' && $e->getFeedbackStatusMessage() === EngineBlock_Corto_Module_Bindings::SAML_STATUS_MESSAGE_EMPTY):
                // invalid loa level
                // should continue if no valid token is allowed

                $log->warning('Unmet loa level for Stepup authentication callout, trying allow no token', array('key_id' => $receivedRequest->getId(), 'result' => $e->getFeedbackInfo()));

                // check if no token allowed
                $processStep = $this->_processingStateHelper->getStepByRequestId($receivedRequest->getId(), ProcessingStateHelperInterface::STEP_STEPUP);
                $originalReceivedResponse = $processStep->getResponse();
                $originalReceivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse);

                $idp = $this->_server->getRepository()->fetchIdentityProviderByEntityId($originalReceivedResponse->getIssuer());
                $sp = $this->_server->getRepository()->fetchServiceProviderByEntityId($originalReceivedRequest->getIssuer());

                // When dealing with an SP that acts as a trusted proxy, we should use the proxying SP and not the proxy itself.
                if ($sp->getCoins()->isTrustedProxy()) {
                    // Overwrite the trusted proxy SP instance with that of the SP that uses the trusted proxy.
                    $sp = $this->_server->findOriginalServiceProvider($receivedRequest, $log);
                }

                if ($this->_stepupGatewayCallOutHelper->allowNoToken($idp, $sp)) {

                    $log->warning('Allow no token allowed from sp/idp configuration, continuing', array('key_id' => $receivedRequest->getId(), 'result' => $e->getFeedbackInfo()));
                    return;
                }

                throw new EngineBlock_Corto_Exception_InvalidStepupLoaLevel(
                    'Invalid loa level encountered during Stepup authentication callout',
                    $e
                );
        }

        $log->warning('Invalid status returned from Stepup authentication gateway', array('key_id' => $receivedRequest->getId(), 'result' => $e->getFeedbackInfo()));

        throw new EngineBlock_Corto_Exception_InvalidStepupCalloutResponse(
            sprintf(
                "Invalid status received from stepup gateway: '%s' '%s' ",
                $e->getFeedbackStatusCode(),
                $e->getFeedbackStatusMessage()
            ),
            $e
        );
    }

    /**
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $receivedRequest
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $receivedResponse
     * @param string $loa
     * @throws EngineBlock_Corto_Module_Services_SessionLostException
     */
    private function updateProcessingStateLoa(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $receivedRequest, EngineBlock_Saml2_ResponseAnnotationDecorator $receivedResponse, $loa)
    {
        $processStep = $this->_processingStateHelper->getStepByRequestId($receivedRequest->getId(), ProcessingStateHelperInterface::STEP_STEPUP);
        $processStep->getResponse()->getAssertion()->setAuthnContextClassRef($loa);
        $this->_processingStateHelper->updateStepResponseByRequestId($receivedRequest->getId(), ProcessingStateHelperInterface::STEP_STEPUP, $processStep->getResponse());
    }
}
