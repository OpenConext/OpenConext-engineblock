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
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

class ErrorReporter
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        LoggerInterface $logger,
        RequestStack $requestStack,
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->logger = $logger;
        $this->requestStack = $requestStack;
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
        // Store some valuable debug info in session so it can be displayed on feedback pages
        try {
            $session  = $this->requestStack->getSession();
            $feedback = $session->get('feedbackInfo') ?: [];

            if ($exception instanceof EngineBlock_Corto_Exception_HasFeedbackInfoInterface) {
                $feedback = array_merge($feedback, $exception->getFeedbackInfo());
            } elseif ($exception instanceof EngineBlock_Corto_Exception_PEPNoAccess) {
                $session->set('error_authorization_policy_decision', $exception->getPolicyDecision());
            }

            $session->set('feedbackInfo', array_merge(
                $feedback,
                $this->engineBlockApplicationSingleton->collectFeedbackInfo($exception)
            ));
        } catch (SessionNotFoundException $e) {
            // No active session (e.g. CLI or recursive error during boot); skip session write
        }
    }
}
