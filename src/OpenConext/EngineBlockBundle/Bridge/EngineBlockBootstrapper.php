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
use OpenConext\EngineBlock\Request\CorrelationIdService;
use OpenConext\EngineBlock\Request\CurrentCorrelationId;
use OpenConext\EngineBlock\Service\FeedbackInfoCollectorInterface;
use OpenConext\EngineBlock\Service\FeedbackStateHelperInterface;
use OpenConext\EngineBlock\Service\Wayf\RememberedIdpCookie;
use OpenConext\EngineBlockBridge\Logger\LoginLogger;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfigurationInterface;
use OpenConext\EngineBlockBundle\Service\WayfRenderer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class EngineBlockBootstrapper implements EventSubscriberInterface
{
    private const REMEMBER_CHOICE_PER_IDP_FEATURE = 'eb.feature_enable_wayf_remember_choice_per_idp';

    private readonly DiContainerRuntime $diContainerRuntime;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Environment $twig,
        WayfRenderer $wayfRenderer,
        CorrelationIdService $correlationIdService,
        CurrentCorrelationId $currentCorrelationId,
        FeedbackStateHelperInterface $feedbackStateHelper,
        FeedbackInfoCollectorInterface $feedbackInfoCollector,
        LoginLogger $loginLogger,
        RememberedIdpCookie $rememberedIdpCookie,
        FeatureConfigurationInterface $featureConfiguration,
        array $preferredIdpEntityIds = [],
    ) {
        $this->diContainerRuntime = new DiContainerRuntime(
            $twig,
            $wayfRenderer,
            $correlationIdService,
            $currentCorrelationId,
            $feedbackStateHelper,
            $feedbackInfoCollector,
            $loginLogger,
            $rememberedIdpCookie,
            $featureConfiguration->hasFeature(self::REMEMBER_CHOICE_PER_IDP_FEATURE)
                && $featureConfiguration->isEnabled(self::REMEMBER_CHOICE_PER_IDP_FEATURE),
            $preferredIdpEntityIds,
        );
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
