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

namespace OpenConext\EngineBlockBundle\EventListener;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Attributes_Manipulator_CustomException;
use EngineBlock_Corto_Exception_AuthnContextClassRefBlacklisted;
use EngineBlock_Corto_Exception_InvalidAcsLocation;
use EngineBlock_Corto_Exception_InvalidMfaAuthnContextClassRef;
use EngineBlock_Corto_Exception_InvalidStepupCalloutResponse;
use EngineBlock_Corto_Exception_InvalidStepupLoaLevel;
use EngineBlock_Corto_Exception_MissingRequiredFields;
use EngineBlock_Corto_Exception_PEPNoAccess;
use EngineBlock_Corto_Exception_ReceivedErrorStatusCode;
use EngineBlock_Corto_Exception_UnknownPreselectedIdp;
use EngineBlock_Corto_Exception_InvalidAttributeValue;
use EngineBlock_Corto_Exception_UserCancelledStepupCallout;
use EngineBlock_Corto_Module_Bindings_SignatureVerificationException;
use EngineBlock_Corto_Module_Bindings_UnableToReceiveMessageException;
use EngineBlock_Corto_Module_Bindings_UnsupportedAcsLocationSchemeException;
use EngineBlock_Corto_Module_Bindings_UnsupportedBindingException;
use EngineBlock_Corto_Module_Bindings_UnsupportedSignatureMethodException;
use EngineBlock_Corto_Module_Bindings_VerificationException;
use EngineBlock_Corto_Module_Service_SingleSignOn_NoIdpsException;
use EngineBlock_Corto_Module_Services_SessionLostException;
use EngineBlock_Corto_Module_Services_SessionNotStartedException;
use EngineBlock_Exception_UnknownRequesterIdInAuthnRequest;
use EngineBlock_Exception_UnknownIdentityProvider;
use EngineBlock_Exception_UnknownServiceProvider;
use OpenConext\EngineBlock\Exception\InvalidBindingException;
use OpenConext\EngineBlock\Exception\InvalidRequestMethodException;
use OpenConext\EngineBlock\Exception\MissingParameterException;
use OpenConext\EngineBlockBridge\ErrorReporter;
use OpenConext\EngineBlockBundle\Exception\EntityCanNotBeFoundException;
use OpenConext\EngineBlockBundle\Exception\StuckInAuthenticationLoopException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 *
 * All due to this being a catch all; will be refactored, see https://www.pivotaltracker.com/story/show/107565968
 */
class RedirectToFeedbackPageExceptionListener
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var ErrorReporter
     */
    private $errorReporter;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        UrlGeneratorInterface $urlGenerator,
        ErrorReporter $errorReporter,
        LoggerInterface $logger
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->logger = $logger;
        $this->urlGenerator = $urlGenerator;
        $this->errorReporter = $errorReporter;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength) - See comment in class doc block
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $redirectParams = [];
        if ($exception instanceof EngineBlock_Corto_Module_Bindings_UnableToReceiveMessageException) {
            $message         = 'Unable to receive message';
            $redirectToRoute = 'authentication_feedback_unable_to_receive_message';
        } elseif ($exception instanceof EngineBlock_Corto_Module_Services_SessionLostException) {
            $message         = 'Session lost';
            $redirectToRoute = 'authentication_feedback_session_lost';
        } elseif ($exception instanceof EngineBlock_Corto_Module_Services_SessionNotStartedException) {
            $message         = 'Session not started';
            $redirectToRoute = 'authentication_feedback_session_not_started';
        } elseif ($exception instanceof EngineBlock_Corto_Module_Service_SingleSignOn_NoIdpsException) {
            $message         = 'No Identity Provider';
            $redirectToRoute = 'authentication_feedback_no_idps';
        } elseif ($exception instanceof EngineBlock_Corto_Exception_InvalidAcsLocation) {
            $message         = 'Invalid ACS location';
            $redirectToRoute = 'authentication_feedback_invalid_acs_location';
        } elseif ($exception instanceof EngineBlock_Corto_Exception_MissingRequiredFields) {
            $message         = 'Missing Required Fields';
            $redirectToRoute = 'authentication_feedback_missing_required_fields';
        } elseif ($exception instanceof EngineBlock_Corto_Exception_AuthnContextClassRefBlacklisted) {
            $message = $exception->getMessage();
            $redirectToRoute = 'authentication_authn_context_class_ref_blacklisted';
        } elseif ($exception instanceof EngineBlock_Corto_Exception_InvalidMfaAuthnContextClassRef) {
            $message         = $exception->getMessage();
            $redirectToRoute = 'authentication_invalid_mfa_authn_context_class_ref';
        } elseif ($exception instanceof EngineBlock_Attributes_Manipulator_CustomException) {
            // @todo this must be done differently, for now don't see how as state is managed by EB.
            $event->getRequest()->getSession()->set('feedback_custom', $exception->getFeedback());
            $message         = 'Custom Exception thrown from Attribute Manipulator';
            $redirectToRoute = 'authentication_feedback_custom';
        } elseif ($exception instanceof EngineBlock_Corto_Module_Bindings_UnsupportedBindingException) {
            $message         = 'Unsupported Binding';
            $redirectToRoute = 'authentication_feedback_invalid_acs_binding';
        } elseif ($exception instanceof EngineBlock_Corto_Module_Bindings_UnsupportedSignatureMethodException) {
            $message         = 'Unsupported signature method';
            $redirectToRoute = 'authentication_feedback_unsupported_signature_method';
            $redirectParams  = [
                'signature-method' => $exception->getSignatureMethod(),
            ];
        } elseif ($exception instanceof EngineBlock_Corto_Module_Bindings_UnsupportedAcsLocationSchemeException) {
            $message         = 'Unsupported URI scheme in ACS location';
            $redirectToRoute = 'authentication_feedback_unsupported_acs_location_uri_scheme';
        } elseif ($exception instanceof EngineBlock_Corto_Exception_ReceivedErrorStatusCode) {
            $message         = 'Received Error Status Code';
            $redirectToRoute = 'authentication_feedback_received_error_status_code';
        } elseif ($exception instanceof EngineBlock_Corto_Module_Bindings_SignatureVerificationException) {
            $message         = 'Unable to verify signature, cert wrong?';
            $redirectToRoute = 'authentication_feedback_signature_verification_failed';
        } elseif ($exception instanceof EngineBlock_Corto_Module_Bindings_VerificationException) {
            $message         = 'Unable to verify message';
            $redirectToRoute = 'authentication_feedback_verification_failed';
        } elseif ($exception instanceof EngineBlock_Exception_UnknownServiceProvider) {
            $message         = 'Unknown Service Provider';
            $redirectToRoute = 'authentication_feedback_unknown_service_provider';

            $redirectParams  = [
                'entity-id'   => $exception->getEntityId(),
                'destination' => $exception->getDestination()
            ];
        } elseif ($exception instanceof EngineBlock_Exception_UnknownIdentityProvider) {
            $message         = 'Unknown Identity Provider';
            $redirectToRoute = 'authentication_feedback_unknown_identity_provider';

            $redirectParams  = [
                'entity-id'   => $exception->getEntityId(),
                'destination' => $exception->getDestination()
            ];
        } elseif ($exception instanceof EngineBlock_Exception_UnknownRequesterIdInAuthnRequest) {
            $message         = 'Encountered unknown RequesterID for the Service Provider (transparant proxying)';
            $redirectToRoute = 'authentication_feedback_unknown_requesterid_in_authnrequest';
        } elseif ($exception instanceof EngineBlock_Corto_Exception_PEPNoAccess) {
            $message         = 'PEP authorization rule violation';
            $redirectToRoute = 'authentication_feedback_pep_violation';
        } elseif ($exception instanceof EngineBlock_Corto_Exception_UnknownPreselectedIdp) {
            $message         = $exception->getMessage();
            $redirectToRoute = 'authentication_feedback_unknown_preselected_idp';

            $redirectParams = ['idp-hash' => $exception->getRemoteIdpMd5Hash()];
        } elseif ($exception instanceof EngineBlock_Corto_Exception_InvalidAttributeValue) {
            $message         = $exception->getMessage();
            $redirectToRoute = 'authentication_feedback_invalid_attribute_value';
        } elseif ($exception instanceof StuckInAuthenticationLoopException) {
            $message         = 'Stuck in authentication loop';
            $redirectToRoute = 'authentication_feedback_stuck_in_authentication_loop';
        } elseif ($exception instanceof InvalidRequestMethodException ||
            $exception instanceof InvalidBindingException ||
            $exception instanceof  MissingParameterException
        ) {
            $message = $exception->getMessage();
            $event->getRequest()->getSession()->set('feedback_custom', $exception->getMessage());
            $redirectToRoute = 'authentication_feedback_no_authentication_request_received';
        } elseif ($exception instanceof \EngineBlock_Corto_Module_Bindings_ClockIssueException) {
            $message = $exception->getMessage();
            $redirectToRoute = 'authentication_feedback_response_clock_issue';
        } elseif ($exception instanceof EngineBlock_Corto_Exception_UserCancelledStepupCallout) {
            $message = $exception->getMessage();
            $redirectToRoute = 'authentication_feedback_stepup_callout_user_cancelled';
        } else if ($exception instanceof EngineBlock_Corto_Exception_InvalidStepupLoaLevel) {
            $message = $exception->getMessage();
            $redirectToRoute = 'authentication_feedback_stepup_callout_unmet_loa';
        } else if ($exception instanceof EngineBlock_Corto_Exception_InvalidStepupCalloutResponse) {
            $message = $exception->getMessage();
            $redirectToRoute = 'authentication_feedback_stepup_callout_unknown';
        } else if ($exception instanceof EntityCanNotBeFoundException) {
            $event->getRequest()->getSession()->set('feedback_custom', $exception->getMessage());
            $redirectToRoute = 'authentication_feedback_metadata_entity_not_found';
        } else {
            return;
        }

        $this->logger->debug(sprintf(
            'Caught Exception "%s":"%s", redirecting to route "%s"',
            get_class($exception),
            $exception->getMessage(),
            $redirectToRoute
        ));

        $this->logger->notice($message);

        $this->errorReporter->reportError($exception, '-> Redirecting to feedback page');

        $event->setResponse(new RedirectResponse(
            $this->urlGenerator->generate($redirectToRoute, $redirectParams, UrlGeneratorInterface::ABSOLUTE_PATH)
        ));
    }
}
