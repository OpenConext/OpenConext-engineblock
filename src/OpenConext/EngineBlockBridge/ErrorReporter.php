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

namespace OpenConext\EngineBlockBridge;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Exception_HasFeedbackInfoInterface;
use EngineBlock_Corto_Exception_PEPNoAccess;
use EngineBlock_Exception;
use Exception;
use OpenConext\EngineBlock\Service\FeedbackInfoCollectorInterface;
use OpenConext\EngineBlock\Service\FeedbackStateHelperInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ErrorReporter
{
    private LoggerInterface $logger;
    private EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton;
    private RequestStack $requestStack;
    private FeedbackStateHelperInterface $feedbackStateHelper;
    private FeedbackInfoCollectorInterface $feedbackInfoCollector;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        LoggerInterface $logger,
        RequestStack $requestStack,
        FeedbackStateHelperInterface $feedbackStateHelper,
        FeedbackInfoCollectorInterface $feedbackInfoCollector,
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->logger = $logger;
        $this->requestStack = $requestStack;
        $this->feedbackStateHelper = $feedbackStateHelper;
        $this->feedbackInfoCollector = $feedbackInfoCollector;
    }

    public function reportError(Exception $exception, string $messageSuffix): void
    {
        $logContext = $this->buildLogContext($exception);
        $severity   = $exception instanceof EngineBlock_Exception
            ? $exception->getSeverity()
            : EngineBlock_Exception::CODE_ERROR;

        $message = $exception->getMessage() ?: 'Exception without message "' . get_class($exception) . '"';
        if ($messageSuffix) {
            $message .= ' | ' . $messageSuffix;
        }

        $this->logger->log($severity, $message, $logContext);

        try {
            $this->storeSessionFeedback($exception);
        } finally {
            // flush all messages in queue, something went wrong!
            $this->engineBlockApplicationSingleton->flushLog('An error was caught');
        }
    }

    private function buildLogContext(Exception $exception): array
    {
        $logContext    = ['exception' => $exception];
        $prevException = $exception;

        // unwrap the exception stack
        while ($prevException = $prevException->getPrevious()) {
            $logContext['previous_exceptions'][] = (string) $prevException;
        }

        return $logContext;
    }

    private function storeSessionFeedback(Exception $exception): void
    {
        // Store some valuable debug info in session so it can be displayed on feedback pages.
        // This is only applicable to web requests; skip entirely in CLI context or when there is no active request.
        if ($this->requestStack->getCurrentRequest() === null) {
            return;
        }

        $session = $this->requestStack->getSession();

        if ($exception instanceof EngineBlock_Corto_Exception_PEPNoAccess) {
            $session->set('error_authorization_policy_decision', $exception->getPolicyDecision());
        }

        $feedback = $this->feedbackInfoCollector->collect($exception);

        if ($exception instanceof EngineBlock_Corto_Exception_HasFeedbackInfoInterface) {
            $feedback = array_merge($feedback, $exception->getFeedbackInfo());
        }

        $this->feedbackStateHelper->storeFeedbackInfo($feedback);
    }
}
