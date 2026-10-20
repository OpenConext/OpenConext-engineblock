<?php

/**
 * Copyright 2026 SURFnet B.V.
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

namespace Tests\OpenConext\EngineBlockBundle;

use OpenConext\EngineBlock\Request\CorrelationIdRepository;
use OpenConext\EngineBlock\Request\CorrelationIdService;
use OpenConext\EngineBlock\Request\CurrentCorrelationId;
use OpenConext\EngineBlock\Service\CookieService;
use OpenConext\EngineBlock\Service\FeedbackInfoCollectorInterface;
use OpenConext\EngineBlock\Service\FeedbackStateHelperInterface;
use OpenConext\EngineBlock\Service\TimeProvider\TimeProvider;
use OpenConext\EngineBlock\Service\Wayf\RememberedIdpCookie;
use OpenConext\EngineBlockBridge\Logger\LoginLogger;
use OpenConext\EngineBlockBundle\Bridge\DiContainerRuntime;
use OpenConext\EngineBlockBundle\Bridge\EngineBlockBootstrapper;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfigurationInterface;
use OpenConext\EngineBlockBundle\Service\WayfRenderer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class EngineBlockBootstrapperTest extends TestCase
{
    #[Test]
    public function it_enables_remember_choice_per_idp_when_the_feature_is_enabled(): void
    {
        $featureConfiguration = $this->createStub(FeatureConfigurationInterface::class);
        $featureConfiguration->method('hasFeature')
            ->with('eb.feature_enable_wayf_remember_choice_per_idp')
            ->willReturn(true);
        $featureConfiguration->method('isEnabled')
            ->with('eb.feature_enable_wayf_remember_choice_per_idp')
            ->willReturn(true);

        $bootstrapper = $this->bootstrapperFactory($featureConfiguration);

        $this->assertTrue($this->diContainerRuntimeOf($bootstrapper)->rememberChoicePerIdp);
    }

    #[Test]
    public function it_disables_remember_choice_per_idp_when_the_feature_is_disabled(): void
    {
        $featureConfiguration = $this->createStub(FeatureConfigurationInterface::class);
        $featureConfiguration->method('hasFeature')
            ->with('eb.feature_enable_wayf_remember_choice_per_idp')
            ->willReturn(true);
        $featureConfiguration->method('isEnabled')
            ->with('eb.feature_enable_wayf_remember_choice_per_idp')
            ->willReturn(false);

        $bootstrapper = $this->bootstrapperFactory($featureConfiguration);

        $this->assertFalse($this->diContainerRuntimeOf($bootstrapper)->rememberChoicePerIdp);
    }

    #[Test]
    public function it_disables_remember_choice_per_idp_when_the_feature_is_unknown(): void
    {
        $featureConfiguration = $this->createStub(FeatureConfigurationInterface::class);
        $featureConfiguration->method('hasFeature')
            ->with('eb.feature_enable_wayf_remember_choice_per_idp')
            ->willReturn(false);

        $bootstrapper = $this->bootstrapperFactory($featureConfiguration);

        $this->assertFalse($this->diContainerRuntimeOf($bootstrapper)->rememberChoicePerIdp);
    }

    private function bootstrapperFactory(FeatureConfigurationInterface $featureConfiguration): EngineBlockBootstrapper
    {
        return new EngineBlockBootstrapper(
            $this->createStub(Environment::class),
            $this->createStub(WayfRenderer::class),
            new CorrelationIdService(new CorrelationIdRepository(new RequestStack()), new CurrentCorrelationId()),
            new CurrentCorrelationId(),
            $this->createStub(FeedbackStateHelperInterface::class),
            $this->createStub(FeedbackInfoCollectorInterface::class),
            $this->createStub(LoginLogger::class),
            $this->rememberedIdpCookieStub(),
            $featureConfiguration,
        );
    }

    private function diContainerRuntimeOf(EngineBlockBootstrapper $bootstrapper): DiContainerRuntime
    {
        $property = new ReflectionProperty(EngineBlockBootstrapper::class, 'diContainerRuntime');

        return $property->getValue($bootstrapper);
    }

    private function rememberedIdpCookieStub(): RememberedIdpCookie
    {
        return new RememberedIdpCookie(
            $this->createStub(TimeProvider::class),
            $this->createStub(CookieService::class),
            7776000,
            16,
            'engine.example.org',
            '/',
            true,
        );
    }
}
