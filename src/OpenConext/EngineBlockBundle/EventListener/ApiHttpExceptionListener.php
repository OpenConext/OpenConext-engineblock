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

use OpenConext\EngineBlockBundle\Http\Exception\ApiHttpException;
use OpenConext\EngineBlockBridge\ErrorReporter;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class ApiHttpExceptionListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ErrorReporter
     */
    private $errorReporter;

    /**
     * @param LoggerInterface $logger
     * @param ErrorReporter   $errorReporter
     */
    public function __construct(LoggerInterface $logger, ErrorReporter $errorReporter)
    {
        $this->logger = $logger;
        $this->errorReporter = $errorReporter;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof ApiHttpException) {
            return;
        }

        $this->logger->warning($exception->getMessage());
        $this->errorReporter->reportError($exception, '-> responding directly');

        $event->setResponse(new JsonResponse($exception->getMessage(), $exception->getStatusCode()));
    }
}
