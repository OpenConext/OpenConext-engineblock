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
use EngineBlock_Corto_Exception_PEPNoAccess;
use EngineBlock_Corto_Exception_HasFeedbackInfoInterface;
use EngineBlock_Exception;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
     * @var SessionInterface
     */
    private $session;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        LoggerInterface $logger,
        RequestStack $requestStack
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->logger = $logger;
        $this->session = $requestStack->getSession();
    }

    /**
     * @param Exception $exception
     * @param string    $messageSuffix
     */
    public function reportError(Exception $exception, $messageSuffix)
    {
        $logContext = ['exception' => $exception];

        if ($exception instanceof EngineBlock_Exception) {
            $severity = $exception->getSeverity();
        } else {
            $severity = EngineBlock_Exception::CODE_ERROR;
        }

        // unwrap the exception stack
        $prevException = $exception;
        while ($prevException = $prevException->getPrevious()) {
            if (!isset($logContext['previous_exceptions'])) {
                $logContext['previous_exceptions'] = [];
            }

            $logContext['previous_exceptions'][] = (string)$prevException;
        }

        // message building
        $message = $exception->getMessage();
        if (empty($message)) {
            $message = 'Exception without message "' . get_class($exception) . '"';
        }

        if ($messageSuffix) {
            $message .= ' | ' . $messageSuffix;
        }

        $this->logger->log($severity, $message, $logContext);

        // Store some valuable debug info in session so it can be displayed on feedback pages
        $feedback = $this->session->get('feedbackInfo');
        if (empty($feedback)) {
            $feedback = [];
        }

        if ($exception instanceof EngineBlock_Corto_Exception_HasFeedbackInfoInterface) {
            $feedback = array_merge($feedback, $exception->getFeedbackInfo());
        } elseif ($exception instanceof EngineBlock_Corto_Exception_PEPNoAccess) {
            $this->session->set('error_authorization_policy_decision', $exception->getPolicyDecision());
        }

        $this->session->set('feedbackInfo', array_merge(
            $feedback,
            $this->engineBlockApplicationSingleton->collectFeedbackInfo($exception)
        ));

        // flush all messages in queue, something went wrong!
        $this->engineBlockApplicationSingleton->flushLog('An error was caught');
    }
}
