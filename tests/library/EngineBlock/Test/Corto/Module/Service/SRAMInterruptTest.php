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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ConsentSettings;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\InMemoryMetadataRepository;
use OpenConext\EngineBlock\Service\AuthenticationStateHelperInterface;
use OpenConext\EngineBlock\Service\ConsentServiceInterface;
use OpenConext\EngineBlock\Service\Dto\ProcessingStateStep;
use OpenConext\EngineBlock\Service\ProcessingStateHelper;
use OpenConext\EngineBlock\Service\ProcessingStateHelperInterface;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationStateInterface;
use OpenConext\EngineBlockBundle\Service\DiscoverySelectionService;
use PHPUnit\Framework\TestCase;
use SAML2\Assertion;
use SAML2\AuthnRequest;
use SAML2\Response;
use SAML2\XML\saml\Issuer;
use SAML2\XML\saml\NameID;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class EngineBlock_Test_Corto_Module_Service_SRAMInterruptTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var EngineBlock_Corto_XmlToArray */
    private $xmlConverterMock;

    /** @var EngineBlock_Corto_Model_Consent_Factory */
    private $consentFactoryMock;

    /** @var EngineBlock_Corto_Model_Consent */
    private $consentMock;

    /** @var ConsentServiceInterface */
    private $consentService;

    /** @var EngineBlock_Corto_ProxyServer */
    private $proxyServerMock;

    /** @var Twig_Environment */
    private $twig;

    /** @var AuthenticationStateHelperInterface|Mock */
    private $authStateHelperMock;

    /**
     * @var ProcessingStateHelperInterface
     */
    private $processingStateHelperMock;

    /**
     * @var Response
     */
    private $sspResponseMock;

    /**
     * @var Request
     */
    private $httpRequestMock;

    /**
     * @var MockArraySessionStorage
     */
    private $sessionMock;

    /**
     * @var DiscoverySelectionService
     */
    private $discoverySelectionService;

    public function setUp(): void
    {
        $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();

        $this->sspResponseMock = $this->mockSspResponse();
        $this->proxyServerMock = $this->mockProxyServer();
        $this->xmlConverterMock = $this->mockXmlConverter($diContainer->getXmlConverter());
        $this->consentFactoryMock = $diContainer->getConsentFactory();
        $this->consentMock = $this->mockConsent();
        $this->consentService = $this->mockConsentService();
        $this->authStateHelperMock = $this->mockAuthStateHelper();
        $this->twig = $this->mockTwig();
        $this->processingStateHelperMock = $this->mockProcessingStateHelper();
        $this->httpRequestMock = $this->mockHttpRequest();
        $this->discoverySelectionService = Phake::mock(DiscoverySelectionService::class);
    }

    public function testConsentRequested()
    {
        // TODO implement using consent as example.
    }

}
