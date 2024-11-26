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

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider as ServiceProvider;
use OpenConext\EngineBlock\Metadata\Loa;
use OpenConext\EngineBlock\Metadata\LoaRepository;
use OpenConext\EngineBlock\Service\ProcessingStateHelperInterface;
use OpenConext\EngineBlock\Stepup\StepupDecision;
use OpenConext\EngineBlock\Stepup\StepupGatewayCallOutHelper;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationState;
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

    /**
     * @var LoaRepository
     */
    private $_loaRepository;

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        Session $session,
        ProcessingStateHelperInterface $processingStateHelper,
        StepupGatewayCallOutHelper $stepupGatewayCallOutHelper,
        LoaRepository $loaRepository
    ) {
        $this->_server = $server;
        $this->_session = $session;
        $this->_processingStateHelper = $processingStateHelper;
        $this->_stepupGatewayCallOutHelper = $stepupGatewayCallOutHelper;
        $this->_loaRepository = $loaRepository;
    }

    /**
     * @param $serviceName
     * @param Request $httpRequest
     */
    public function serve($serviceName, Request $httpRequest)
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();
        $log = $application->getLogInstance();

        // EngineBlock will verify the incoming assertion's Audience which will
        // be set to the entityID it used on the outgoing AuthNRequest, so this
        // place will also need to handle the override if present.
        $serviceEntityId = $this->determineRemoteSpEntityId();
        $expectedDestination = $this->_server->getUrl('stepupAssertionConsumerService');

        $checkResponseSignature = true;
        try {
            $receivedResponse = $this->_server->getBindingsModule()->receiveResponse($serviceEntityId, $expectedDestination);
            $receivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse);

            $this->verifyReceivedLoa($receivedRequest, $receivedResponse, $log);

            // Update the AuthnContextClassRef to the loa returned
            $receivedLoa = $this->_stepupGatewayCallOutHelper->getEbLoa($receivedResponse->getAssertion()->getAuthnContextClassRef());
            $this->updateProcessingStateLoa($receivedRequest, $receivedResponse, $receivedLoa);

            $log->warning('After Stepup authentication update received LoA', ['result' => $receivedLoa]);
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
            $log->warning('After failed Stepup authentication set LoA to Loa1', ['result' => $mappedLoa]);
        }

        if ($checkResponseSignature) {
            $this->_server->checkResponseSignatureMethods($receivedResponse);
        }

        // Verify the SP requester chain.
        // Retrieve the SP again to verify the requester chain. This verification step requires the furthest in chain SP
        // first, thats why the self::getSp method is not used here.
        $sp = $this->_server->getRepository()->fetchServiceProviderByEntityId($receivedRequest->getIssuer()->getValue());
        EngineBlock_SamlHelper::getSpRequesterChain(
            $sp,
            $receivedRequest,
            $this->_server->getRepository()
        );

        $log->info('Handled Stepup authentication callout successfully');

        // Get active request
        $processStep = $this->_processingStateHelper->getStepByRequestId($receivedRequest->getId(), ProcessingStateHelperInterface::STEP_STEPUP);
        $originalReceivedResponse = $processStep->getResponse();

        // Only non-error responses will have a NameID in them
        if ($checkResponseSignature) {
            $this->verifyReceivedNameID($originalReceivedResponse, $receivedResponse);
        }

        $nextProcessStep = $this->_processingStateHelper->getStepByRequestId(
            $receivedRequest->getId(),
            ProcessingStateHelperInterface::STEP_CONSENT
        );


        $this->_server->sendConsentAuthenticationRequest($originalReceivedResponse, $receivedRequest, $nextProcessStep->getRole(), $this->getAuthenticationState());

        return;
    }

    /**
     * Returns the `stepupMetadataService` if no override is defined.
     * To define an override (for StepUp key rollover) configure:
     * `eb.stepup.sfo.override_engine_entityid`. See UPGRADING.md (6.13 -> 6.14)
     * for details.
     *
     * @return string
     */
    private function determineRemoteSpEntityId()
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();
        $container = $application->getDiContainer();
        $entityIdOverrideValue = $container->getStepupEntityIdOverrideValue();
        $features = $container->getFeatureConfiguration();
        $isConfigured = $features->hasFeature('eb.stepup.sfo.override_engine_entityid');
        $isEnabled = $features->isEnabled('eb.stepup.sfo.override_engine_entityid');

        $serviceEntityId = $this->_server->getUrl('stepupMetadataService');
        if ($isEnabled && $isConfigured) {
            return $entityIdOverrideValue;
        }
        return $serviceEntityId;
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
                $log->info('User cancelled Stepup authentication callout', ['result' => $e->getFeedbackInfo()]);

                throw new EngineBlock_Corto_Exception_UserCancelledStepupCallout(
                    'User cancelled Stepup authentication callout',
                    $e
                );

            case ($e->getFeedbackStatusCode() == 'Responder/NoAuthnContext' && $e->getFeedbackStatusMessage() === EngineBlock_Corto_Module_Bindings::SAML_STATUS_MESSAGE_EMPTY):
                // invalid loa level
                // should continue if no valid token is allowed

                $log->warning('Unmet loa level for Stepup authentication callout, trying allow no token', ['result' => $e->getFeedbackInfo()]);

                // check if no token allowed
                $processStep = $this->_processingStateHelper->getStepByRequestId($receivedRequest->getId(), ProcessingStateHelperInterface::STEP_STEPUP);
                $originalReceivedResponse = $processStep->getResponse();
                $originalReceivedRequest = $this->_server->getReceivedRequestFromResponse($receivedResponse);

                $idp = $this->_server->getRepository()->fetchIdentityProviderByEntityId($originalReceivedResponse->getIssuer()->getValue());
                $sp = $this->getSp($originalReceivedRequest, $log);

                if ($this->_stepupGatewayCallOutHelper->allowNoToken($idp, $sp)) {

                    $log->warning('Allow no token allowed from sp/idp configuration, continuing', ['result' => $e->getFeedbackInfo()]);
                    return;
                }

                throw new EngineBlock_Corto_Exception_InvalidStepupLoaLevel(
                    'Invalid loa level encountered during Stepup authentication callout',
                    $e
                );
        }

        $log->warning('Invalid status returned from Stepup authentication gateway', ['result' => $e->getFeedbackInfo()]);

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
     * @param LoggerInterface $log
     * @return ServiceProvider
     */
    private function getSp(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $receivedRequest, LoggerInterface $log)
    {
        $sp = $this->_server->getRepository()->fetchServiceProviderByEntityId($receivedRequest->getIssuer()->getValue());

        // When dealing with an SP that acts as a trusted proxy, we should use the proxying SP and not the proxy itself.
        if ($sp->getCoins()->isTrustedProxy()) {
            // Overwrite the trusted proxy SP instance with that of the SP that uses the trusted proxy.
            $sp = $this->_server->findOriginalServiceProvider($receivedRequest, $log);
        }

        return $sp;
    }

    /**
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $receivedRequest
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $receivedResponse
     * @param string $loa
     * @throws EngineBlock_Corto_Module_Services_SessionLostException
     */
    private function updateProcessingStateLoa(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $receivedRequest, EngineBlock_Saml2_ResponseAnnotationDecorator $receivedResponse, Loa $loa)
    {
        $processStep = $this->_processingStateHelper->getStepByRequestId($receivedRequest->getId(), ProcessingStateHelperInterface::STEP_STEPUP);
        $processStep->getResponse()->getAssertion()->setAuthnContextClassRef($loa->getIdentifier());
        $this->_processingStateHelper->updateStepResponseByRequestId($receivedRequest->getId(), ProcessingStateHelperInterface::STEP_STEPUP, $processStep->getResponse());
    }

    /**
     * Verify the requested level of assurance is met (LoA should be equal or greater than the SP/IdP requirement)
     *
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $receivedRequest
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $receivedResponse
     * @return void
     * @throws EngineBlock_Corto_Module_Services_SessionLostException
     * @throws EngineBlock_Exception
     */
    private function verifyReceivedLoa(
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $receivedRequest,
        EngineBlock_Saml2_ResponseAnnotationDecorator $receivedResponse,
        LoggerInterface $log
    ) {
        $log->info('Test if the received LoA meets the LoA requirements stated by the SP or IdP');
        // First get the original issuer (SP)
        $sp = $this->getSp($receivedRequest, $log);
        // Then retrieve the IdP to be able to determine the required SP/IdP LoA requirement
        $originalResponse = $this->_processingStateHelper->getStepByRequestId(
            $receivedRequest->getId(),
            ProcessingStateHelperInterface::STEP_STEPUP
        )->getResponse();
        $idp = $this->_server->getRepository()->fetchIdentityProviderByEntityId(
            $originalResponse->getIssuer()->getValue()
        );

        $pdpLoas = $originalResponse->getPdpRequestedLoas();
        $authnRequestLoas = $receivedRequest->getStepupObligations($this->_loaRepository->getStepUpLoas());

        $stepupDecision = new StepupDecision($idp, $sp, $authnRequestLoas, $pdpLoas, $this->_loaRepository, $log);
        $requiredLoa = $stepupDecision->getStepupLoa();
        $receivedLoa = $this->_stepupGatewayCallOutHelper->getEbLoa(
            $receivedResponse->getAssertion()->getAuthnContextClassRef()
        );

        if (!$receivedLoa->levelIsHigherOrEqualTo($requiredLoa)) {
            throw new EngineBlock_Exception(
                sprintf(
                    'Stepup authentication failed, the required LoA ("%s") was not met. The stepup gateway should have enforced this. Received LoA was "%s"',
                    $requiredLoa->getIdentifier(),
                    $receivedLoa->getIdentifier()
                )
            );
        }
        $log->info('Received a suitable LoA response from the stepup gateway.');
    }

    /**
     * The NameID that the assertion from Stepup reports back, must always match the one we
     * requested Stepup for when sending the request to Stepup. As a defense in depth against
     * any gaps elsewhere, we doublecheck that this indeed matches.
     */
    private function verifyReceivedNameID(
        EngineBlock_Saml2_ResponseAnnotationDecorator $originalReceivedResponse,
        EngineBlock_Saml2_ResponseAnnotationDecorator $stepupReceivedResponse
    ): void {
        if ($originalReceivedResponse->getNameID()->getValue() !== $stepupReceivedResponse->getNameID()->getValue()) {
            throw new EngineBlock_Exception(
                'Stepup authentication failed, the received NameID from Stepup does not match the one we sent out.'
            );
        }
    }
}
