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
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Twig_Environment;

final class MethodNotAllowedHttpExceptionListener
{
    /**
     * @var Twig_Environment
     */
    private $twig;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton
     * @param Twig_Environment $twig
     * @param LoggerInterface $logger
     */
    public function __construct(
        Twig_Environment $twig,
        LoggerInterface $logger
    ) {
        $this->twig = $twig;
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof MethodNotAllowedHttpException) {
            return;
        }

        $request = $event->getRequest();
        $uri = strtok($request->getUri(), '?');
        $requestMethod = $request->getRealMethod();
        $allowedMethods = isset($exception->getHeaders()['Allow']) ? $exception->getHeaders()['Allow'] : 'Unknown';

        // inverted quotes for BC, existing log parsers may rely on this
        $this->logger->notice(sprintf(
            "[405]Disallowed request method: '%s'",
            $requestMethod
        ));

        $response = new Response(
            $this->twig->render(
                '@theme/Default/View/Error/method-not-allowed.html.twig',
                [
                    'requestMethod' => $requestMethod,
                    'allowedMethods' => $allowedMethods,
                    'uri' => $uri
                ]
            ),
            405
        );

        $event->setResponse($response);
        // once we've handled it, we don't want anything else to interfere.
        $event->stopPropagation();
    }
}
