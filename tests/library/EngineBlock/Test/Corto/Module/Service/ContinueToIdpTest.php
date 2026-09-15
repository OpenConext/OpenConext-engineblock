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

use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Factory\Factory\ServiceProviderFactory;
use OpenConext\EngineBlock\Request\CorrelationIdServiceInterface;
use OpenConext\EngineBlock\Request\CurrentCorrelationId;
use OpenConext\EngineBlock\Service\CookieService;
use OpenConext\EngineBlock\Service\FeedbackInfoCollectorInterface;
use OpenConext\EngineBlock\Service\FeedbackStateHelperInterface;
use OpenConext\EngineBlock\Service\TimeProvider\TimeProvider;
use OpenConext\EngineBlock\Service\Wayf\RememberedIdpCookie;
use OpenConext\EngineBlockBridge\Logger\LoginLogger;
use OpenConext\EngineBlockBundle\Bridge\DiContainerRuntime;
use OpenConext\EngineBlockBundle\Service\WayfRenderer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class EngineBlock_Test_Corto_Module_Service_ContinueToIdpTest extends TestCase
{
    private $originalDiContainer;
    private ?DiContainerRuntime $originalDiContainerRuntime = null;

    protected function setUp(): void
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();
        $this->originalDiContainer = $application->getDiContainer();

        try {
            $this->originalDiContainerRuntime = $application->getDiContainerRuntime();
        } catch (RuntimeException) {
            $this->originalDiContainerRuntime = null;
        }
    }

    protected function tearDown(): void
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();
        $this->setDiContainer($application, $this->originalDiContainer);

        if ($this->originalDiContainerRuntime !== null) {
            $application->setDiContainerRuntime($this->originalDiContainerRuntime);
        }
    }

    #[DataProvider('rememberChoiceGateProvider')]
    public function testPersistRememberedChoiceOnlyWritesTheCookieWhenTheGateIsFullyEnabled(
        bool $globalRememberChoiceEnabled,
        bool $rememberChoicePerIdpEnabled,
        bool $spWayfRememberChoice,
        bool $expectCookieWritten
    ): void {
        $application = EngineBlock_ApplicationSingleton::getInstance();

        $diContainer = Phake::mock(EngineBlock_Application_DiContainer::class);
        Phake::when($diContainer)->getRememberChoice()->thenReturn($globalRememberChoiceEnabled);
        Phake::when($diContainer)->getSymfonyRequest()->thenReturn(Request::create('/'));
        $this->setDiContainer($application, $diContainer);

        $cookieService = Phake::mock(CookieService::class);
        Phake::when($cookieService)->setCookieWithSameSite(Phake::anyParameters())->thenReturn(true);

        $rememberedIdpCookie = new RememberedIdpCookie(
            new TimeProvider(),
            $cookieService,
            7776000,
            16,
            'engine.example.org',
            '/',
            true,
        );

        $application->setDiContainerRuntime(new DiContainerRuntime(
            $this->createStub(Environment::class),
            $this->createStub(WayfRenderer::class),
            $this->createStub(CorrelationIdServiceInterface::class),
            new CurrentCorrelationId(),
            $this->createStub(FeedbackStateHelperInterface::class),
            $this->createStub(FeedbackInfoCollectorInterface::class),
            $this->createStub(LoginLogger::class),
            $rememberedIdpCookie,
            $rememberChoicePerIdpEnabled,
        ));

        $proxyServerMock = Phake::mock(EngineBlock_Corto_ProxyServer::class);
        Phake::when($proxyServerMock)->getLogger()->thenReturn(Phake::mock(LoggerInterface::class));

        $service = new EngineBlock_Corto_Module_Service_ContinueToIdp(
            $proxyServerMock,
            Phake::mock(EngineBlock_Corto_XmlToArray::class),
            $this->createStub(Environment::class),
            Phake::mock(ServiceProviderFactory::class),
        );

        $sp = new ServiceProvider('https://sp.example.org', wayfRememberChoice: $spWayfRememberChoice);
        $idp = new IdentityProvider('https://idp.example.org');

        $method = new ReflectionMethod($service, '_persistRememberedChoice');
        $method->invoke($service, $sp, $idp, '1');

        if ($expectCookieWritten) {
            Phake::verify($cookieService)->setCookieWithSameSite(Phake::anyParameters());
        } else {
            Phake::verifyNoInteraction($cookieService);
        }
    }

    public static function rememberChoiceGateProvider(): array
    {
        return [
            'all enabled: cookie is written' => [true, true, true, true],
            'global remember choice disabled: not written' => [false, true, true, false],
            'per-idp feature disabled: not written' => [true, false, true, false],
            'sp opted out: not written' => [true, true, false, false],
            'all disabled: not written' => [false, false, false, false],
        ];
    }

    private function setDiContainer(EngineBlock_ApplicationSingleton $application, $diContainer): void
    {
        $property = new ReflectionProperty(EngineBlock_ApplicationSingleton::class, '_diContainer');
        $property->setValue($application, $diContainer);
    }
}
