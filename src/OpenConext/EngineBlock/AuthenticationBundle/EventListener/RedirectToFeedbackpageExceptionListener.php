<?php

/**
 * Copyright 2015 SURFnet bv
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

namespace OpenConext\EngineBlock\AuthenticationBundle\EventListener;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Exception_InvalidAcsLocation;
use EngineBlock_Corto_Exception_UnknownIssuer;
use EngineBlock_Corto_Exception_UserNotMember;
use EngineBlock_Corto_Module_Bindings_UnableToReceiveMessageException;
use EngineBlock_Corto_Module_Service_SingleSignOn_NoIdpsException;
use EngineBlock_Corto_Module_Services_SessionLostException;
use EngineBlock_Corto_ProxyServer_UnknownRemoteEntityException;
use Exception;
use OpenConext\EngineBlock\CompatibilityBundle\Bridge\ErrorReporter;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RedirectToFeedbackpageExceptionListener
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

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $redirectParams = array();
        if ($exception instanceof EngineBlock_Corto_Module_Bindings_UnableToReceiveMessageException) {
            $message         = 'Unable to receive message';
            $redirectToRoute = 'authentication_feedback_unable_to_receive_message';
        } elseif ($exception instanceof EngineBlock_Corto_Exception_UserNotMember) {
            $message         = 'User is not a member';
            $redirectToRoute = 'authentication_feedback_vo_membership_required';
        } elseif ($exception instanceof EngineBlock_Corto_Module_Services_SessionLostException) {
            $message         = 'Sessions lost';
            $redirectToRoute = 'authentication_feedback_session_lost';
        } elseif ($exception instanceof EngineBlock_Corto_Exception_UnknownIssuer) {
            $message         = 'Unknown Issuer';
            $redirectToRoute = 'authentication_feedback_unknown_issuer';
            $redirectParams  = array(
                'entity-id'   => $exception->getEntityId(),
                'destination' => $exception->getDestination()
            );
        } elseif ($exception instanceof EngineBlock_Corto_Module_Service_SingleSignOn_NoIdpsException) {
            $message         = 'No Identity Provider';
            $redirectToRoute = 'authentication_feedback_no_idps';
        } elseif ($exception instanceof EngineBlock_Corto_Exception_InvalidAcsLocation) {
            $message = 'Invalid ACS location';
            $redirectToRoute = 'authentication_feedback_invalid_acs_location';
        } elseif ($exception instanceof EngineBlock_Corto_ProxyServer_UnknownRemoteEntityException) {
            $message         = 'Unknown Remote Entity';
            $redirectToRoute = 'authentication_feedback_unknown_service_provider';
            $redirectParams  = array('entity-id' => $exception->getEntityId());
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
            $this->urlGenerator->generate($redirectToRoute, $redirectParams)
        ));
    }
}
