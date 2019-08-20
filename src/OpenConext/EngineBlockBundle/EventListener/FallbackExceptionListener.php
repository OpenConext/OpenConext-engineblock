<?php

/**
 * Copyright 2014 SURFnet B.V.
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

use EngineBlock_Exception;
use OpenConext\EngineBlockBridge\ErrorReporter;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * The Dispatcher in the old code wrapped everything in a try/catch to allow for graceful recovery.
 * This listener mimics that behaviour. When phasing out corto, this listener should be replaced by
 * Symfony style custom error pages
 * @see https://www.pivotaltracker.com/story/show/107565968
 *
 * In a later iteration, a custom error page implementation has been implemented. This to allow
 * reloading of the error page. Otherwise, it would be hard to match the 'Unique Request Id'
 * displayed on the error page with the actual request id of the error.
 * @see https://www.pivotaltracker.com/story/show/164076480
 */
class FallbackExceptionListener
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
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @param LoggerInterface $logger
     * @param ErrorReporter $errorReporter
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        LoggerInterface $logger,
        ErrorReporter $errorReporter,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->logger = $logger;
        $this->errorReporter = $errorReporter;
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        $this->logger->debug(sprintf(
            'Caught Exception "%s":"%s"',
            get_class($exception),
            $exception->getMessage()
        ));

        if ($exception instanceof EngineBlock_Exception) {
            $this->errorReporter->reportError($exception, 'Caught Unhandled EngineBlock_Exception');
        } else {
            $this->errorReporter->reportError(
                new EngineBlock_Exception($exception->getMessage(), EngineBlock_Exception::CODE_ERROR, $exception),
                'Caught Unhandled generic exception'
            );
        }

        $redirectToRoute = 'feedback_unknown_error';

        $event->setResponse(new RedirectResponse(
            $this->urlGenerator->generate($redirectToRoute, [], UrlGeneratorInterface::ABSOLUTE_PATH)
        ));
    }
}
