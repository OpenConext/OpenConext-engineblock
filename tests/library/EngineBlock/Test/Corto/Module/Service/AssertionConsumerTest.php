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

use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Factory\Factory\ServiceProviderFactory;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlock\Service\ProcessingStateHelperInterface;
use OpenConext\EngineBlock\Stepup\StepupGatewayCallOutHelper;
use PHPUnit\Framework\TestCase;
use SAML2\Assertion;
use SAML2\Constants;
use SAML2\XML\saml\Issuer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Twig\Environment;

class EngineBlock_Test_Corto_Module_Service_AssertionConsumerTest extends TestCase
{
    private const PROXY_SP_ENTITY_ID = 'https://proxy.example.com';
    private const REAL_SP_ENTITY_ID  = 'https://realsp.example.com';
    private const IDP_ENTITY_ID      = 'https://idp.example.com';
    private const ENGINE_URL         = 'https://engine.example.com/some-service';

    /** @var EngineBlock_Corto_ProxyServer */
    private $proxyServerMock;

    /** @var EngineBlock_Corto_Module_Bindings */
    private $bindingsModuleMock;

    /** @var EngineBlock_Saml2_ResponseAnnotationDecorator */
    private $responseMock;

    /** @var EngineBlock_Saml2_AuthnRequestAnnotationDecorator */
    private $requestMock;

    public function setUp(): void
    {
        $_SERVER['HTTP_HOST'] = 'engine.example.com';

        $this->bindingsModuleMock = Phake::mock('EngineBlock_Corto_Module_Bindings');
        $this->proxyServerMock    = $this->mockProxyServer();
        $this->responseMock       = $this->mockResponse();
        $this->requestMock        = $this->mockRequest();

        Phake::when($this->bindingsModuleMock)
            ->receiveResponse(Phake::anyParameters())
            ->thenReturn($this->responseMock);

        Phake::when($this->proxyServerMock)
            ->getReceivedRequestFromResponse(Phake::anyParameters())
            ->thenReturn($this->requestMock);

        Phake::when($this->proxyServerMock)
            ->checkResponseSignatureMethods(Phake::anyParameters())
            ->thenReturn(null);

        Phake::when($this->proxyServerMock)
            ->filterInputAssertionAttributes(Phake::anyParameters())
            ->thenReturn(null);

        // Stop the serve() flow before it reaches the processing pipeline
        Phake::when($this->proxyServerMock)
            ->addConsentProcessStep(Phake::anyParameters())
            ->thenThrow(new RuntimeException('test-stop'));
    }

    public function testTrustedProxySetsCurrentAndProxySessionVariables(): void
    {
        [$proxySp, $proxyCoins] = $this->buildMockedServiceProvider(self::PROXY_SP_ENTITY_ID, isTrustedProxy: true);
        [$realSp,  $realCoins]  = $this->buildMockedServiceProvider(self::REAL_SP_ENTITY_ID,  isTrustedProxy: false);
        $idp = $this->buildMockedIdentityProvider(self::IDP_ENTITY_ID);

        $repo = $this->buildMockedRepository($proxySp, $realSp, $idp);
        $this->proxyServerMock->setRepository($repo);

        $this->configureRequestIssuer(self::PROXY_SP_ENTITY_ID);

        Phake::when($this->proxyServerMock)
            ->findOriginalServiceProvider(Phake::anyParameters())
            ->thenReturn($realSp);

        $_SESSION = [];

        $this->runServe();

        $this->assertSame(
            self::REAL_SP_ENTITY_ID,
            $_SESSION['currentServiceProvider'],
            'currentServiceProvider must be set to the real SP entity ID behind the trusted proxy'
        );
        $this->assertSame(
            self::PROXY_SP_ENTITY_ID,
            $_SESSION['proxyServiceProvider'],
            'proxyServiceProvider must be set to the trusted proxy SP entity ID'
        );
    }

    public function testNonTrustedSpDoesNotSetProxySessionVariables(): void
    {
        [$regularSp, $regularCoins] = $this->buildMockedServiceProvider(self::REAL_SP_ENTITY_ID, isTrustedProxy: false);
        $idp = $this->buildMockedIdentityProvider(self::IDP_ENTITY_ID);

        $repo = $this->buildMockedRepository($regularSp, $regularSp, $idp);
        $this->proxyServerMock->setRepository($repo);

        $this->configureRequestIssuer(self::REAL_SP_ENTITY_ID);

        $_SESSION = [];

        $this->runServe();

        $this->assertArrayNotHasKey(
            'currentServiceProvider',
            $_SESSION,
            'currentServiceProvider must NOT be written when the SP is not a trusted proxy'
        );
        $this->assertArrayNotHasKey(
            'proxyServiceProvider',
            $_SESSION,
            'proxyServiceProvider must NOT be written when the SP is not a trusted proxy'
        );
    }

    /**
     * Runs serve() and swallows only the deliberate "test-stop" exception injected via
     * addConsentProcessStep(). Any other exception is re-thrown to fail the test.
     */
    private function runServe(): void
    {
        $service = new EngineBlock_Corto_Module_Service_AssertionConsumer(
            $this->proxyServerMock,
            Phake::mock('EngineBlock_Corto_XmlToArray'),
            new Session(new MockArraySessionStorage()),
            Phake::mock(ProcessingStateHelperInterface::class),
            // StepupGatewayCallOutHelper is declared final and cannot be Phake-mocked;
            // use the real instance from the DI container (never called in this code path).
            EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getStepupGatewayCallOutHelper(),
            Phake::mock(ServiceProviderFactory::class)
        );

        try {
            $service->serve('assertionConsumerService', Phake::mock(Request::class));
        } catch (RuntimeException $e) {
            if ($e->getMessage() !== 'test-stop') {
                throw $e;
            }
        }
    }

    private function mockProxyServer(): EngineBlock_Corto_ProxyServer
    {
        $twig = Phake::mock(Environment::class);

        /** @var EngineBlock_Corto_ProxyServer $proxyServerMock */
        $proxyServerMock = Phake::partialMock('EngineBlock_Corto_ProxyServer', $twig);
        $proxyServerMock->setBindingsModule($this->bindingsModuleMock);

        Phake::when($proxyServerMock)
            ->getUrl(Phake::anyParameters())
            ->thenReturn(self::ENGINE_URL);

        return $proxyServerMock;
    }

    private function mockResponse(): EngineBlock_Saml2_ResponseAnnotationDecorator
    {
        /** @var EngineBlock_Saml2_ResponseAnnotationDecorator $responseMock */
        $responseMock = Phake::mock('EngineBlock_Saml2_ResponseAnnotationDecorator');

        // Avoid triggering the transparent-error and no-passive early-return branches
        Phake::when($responseMock)->isTransparentErrorResponse()->thenReturn(false);
        Phake::when($responseMock)->getStatus()->thenReturn([Constants::STATUS_SUCCESS]);

        $idpIssuer = new Issuer();
        $idpIssuer->setValue(self::IDP_ENTITY_ID);
        Phake::when($responseMock)->getIssuer()->thenReturn($idpIssuer);

        // getAssertions()[0] is cloned by serve() before filterInputAssertionAttributes()
        Phake::when($responseMock)->getAssertions()->thenReturn([new Assertion()]);

        return $responseMock;
    }

    private function mockRequest(): EngineBlock_Saml2_AuthnRequestAnnotationDecorator
    {
        /** @var EngineBlock_Saml2_AuthnRequestAnnotationDecorator $requestMock */
        $requestMock = Phake::mock('EngineBlock_Saml2_AuthnRequestAnnotationDecorator');

        Phake::when($requestMock)->isDebugRequest()->thenReturn(false);
        Phake::when($requestMock)->getKeyId()->thenReturn(null);
        Phake::when($requestMock)->wasSigned()->thenReturn(false);
        Phake::when($requestMock)->getRequesterIds()->thenReturn([]);

        return $requestMock;
    }

    private function configureRequestIssuer(string $entityId): void
    {
        $issuer = new Issuer();
        $issuer->setValue($entityId);
        Phake::when($this->requestMock)->getIssuer()->thenReturn($issuer);
    }

    /**
     * @return array{0: ServiceProvider, 1: Coins}
     */
    private function buildMockedServiceProvider(string $entityId, bool $isTrustedProxy): array
    {
        /** @var Coins $coins */
        $coins = Phake::mock(Coins::class);
        Phake::when($coins)->isTrustedProxy()->thenReturn($isTrustedProxy);
        Phake::when($coins)->additionalLogging()->thenReturn(false);

        /** @var ServiceProvider $sp */
        $sp = Phake::mock(ServiceProvider::class);
        $sp->entityId = $entityId;
        Phake::when($sp)->getCoins()->thenReturn($coins);

        return [$sp, $coins];
    }

    private function buildMockedIdentityProvider(string $entityId): IdentityProvider
    {
        /** @var Coins $coins */
        $coins = Phake::mock(Coins::class);
        Phake::when($coins)->additionalLogging()->thenReturn(false);

        /** @var IdentityProvider $idp */
        $idp = Phake::mock(IdentityProvider::class);
        $idp->entityId = $entityId;
        Phake::when($idp)->getCoins()->thenReturn($coins);

        return $idp;
    }

    private function buildMockedRepository(
        ServiceProvider  $spByIssuer,
        ServiceProvider  $spByRealId,
        IdentityProvider $idp
    ): MetadataRepositoryInterface {
        /** @var MetadataRepositoryInterface $repo */
        $repo = Phake::mock(MetadataRepositoryInterface::class);

        Phake::when($repo)
            ->fetchServiceProviderByEntityId($spByIssuer->entityId)
            ->thenReturn($spByIssuer);

        // Also register the real SP so secondary lookups (e.g. inside getSpRequesterChain) resolve
        if ($spByRealId->entityId !== $spByIssuer->entityId) {
            Phake::when($repo)
                ->fetchServiceProviderByEntityId($spByRealId->entityId)
                ->thenReturn($spByRealId);
        }

        Phake::when($repo)
            ->fetchIdentityProviderByEntityId(self::IDP_ENTITY_ID)
            ->thenReturn($idp);

        return $repo;
    }
}