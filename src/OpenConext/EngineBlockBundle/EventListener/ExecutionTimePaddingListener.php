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

use OpenConext\EngineBlockBridge\ErrorReporter;
use OpenConext\EngineBlockBundle\Exception\AddExecutionTimePadding;
use OpenConext\EngineBlockBundle\Value\ExecutionTime;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ExecutionTimePaddingListener
{
    /**
     * @var ExecutionTimeTracker
     */
    private $executionTimeTracker;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ErrorReporter
     */
    private $errorReporter;

    /**
     * @var ExecutionTime
     */
    private $minimumExecutionTime;

    public function __construct(
        ExecutionTimeTracker $executionTimeTracker,
        UrlGeneratorInterface $urlGenerator,
        LoggerInterface $logger,
        ErrorReporter $errorReporter,
        ExecutionTime $minimumExecutionTime
    ) {
        $this->executionTimeTracker = $executionTimeTracker;
        $this->urlGenerator         = $urlGenerator;
        $this->logger               = $logger;
        $this->errorReporter        = $errorReporter;
        $this->minimumExecutionTime = $minimumExecutionTime;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof AddExecutionTimePadding) {
            return;
        }

        if (!$this->executionTimeTracker->isTracking()) {
            return;
        }

        $this->logger->warning(
            sprintf('Handling exception: "%s": "%s"', get_class($exception), $exception->getMessage())
        );

        if ($this->executionTimeTracker->currentExecutionTimeExceeds($this->minimumExecutionTime)) {
            $this->logger->warning(sprintf(
                'Not padding response time: it exceeds the configured padded response time (%d milliseconds)',
                $this->minimumExecutionTime->getExecutionTime()
            ));
        } else {
            $requiredPadding = $this->executionTimeTracker->timeRemainingUntil($this->minimumExecutionTime);

            $this->logger->info(sprintf(
                'Padding response time with %d milliseconds',
                $requiredPadding->getExecutionTime()
            ));

            usleep($requiredPadding->toMicroseconds());
        }

        $message         = 'Unable to verify message';
        $redirectToRoute = 'authentication_feedback_verification_failed';

        $this->logger->debug(sprintf('Redirecting to route "%s"', $redirectToRoute));
        $this->logger->notice($message);
        $this->errorReporter->reportError($exception, '-> Redirecting to feedback page');

        $event->setResponse(new RedirectResponse(
            $this->urlGenerator->generate($redirectToRoute, [], UrlGeneratorInterface::ABSOLUTE_PATH)
        ));
    }
}
