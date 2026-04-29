<?php

/**
 * Copyright 2025 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Bridge;

use EngineBlock_ApplicationSingleton;
use OpenConext\EngineBlock\Service\FeedbackInfoCollectorInterface;
use OpenConext\EngineBlock\Service\FeedbackStateHelperInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class EngineBlockBootstrapper implements EventSubscriberInterface
{
    private readonly DiContainerRuntime $diContainerRuntime;

    public function __construct(
        Environment $twig,
        FeedbackStateHelperInterface $feedbackStateHelper,
        FeedbackInfoCollectorInterface $feedbackInfoCollector,
    ) {
        $this->diContainerRuntime = new DiContainerRuntime($twig, $feedbackStateHelper, $feedbackInfoCollector);
    }

    public function onKernelRequest(): void
    {
        $engineBlock = EngineBlock_ApplicationSingleton::getInstance();
        $engineBlock->setDiContainerRuntime($this->diContainerRuntime);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 100],
        ];
    }
}
