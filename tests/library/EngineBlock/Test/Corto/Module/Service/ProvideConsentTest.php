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
use PHPUnit\Framework\TestCase;
use SAML2\Assertion;
use SAML2\AuthnRequest;
use SAML2\Response;
use SAML2\XML\saml\Issuer;
use SAML2\XML\saml\NameID;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class EngineBlock_Test_Corto_Module_Service_ProvideConsentTest extends TestCase
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
    }

    public function testConsentRequested()
    {
        $this->expectNotToPerformAssertions();

        $provideConsentService = $this->factoryService();

        $provideConsentService->serve(null, $this->httpRequestMock);
    }

    public function testConsentIsSkippedWhenPriorConsentIsStored()
    {
        $provideConsentService = $this->factoryService();

        Phake::when($this->consentMock)
            ->explicitConsentWasGivenFor(Phake::anyParameters())
            ->thenReturn(true);

        $provideConsentService->serve(null, $this->httpRequestMock);

        Phake::verify($this->proxyServerMock->getBindingsModule())
            ->send(Phake::capture($message), Phake::capture($metadata));
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:consent:prior', $message->getConsent());
    }

    public function testConsentIsSkippedWhenGloballyDisabled()
    {
        $this->setCoin($this->proxyServerMock->getRepository()->fetchServiceProviderByEntityId('testSp'), 'isConsentRequired', false);

        $provideConsentService = $this->factoryService();

        $provideConsentService->serve(null, $this->httpRequestMock);

        Phake::verify($this->proxyServerMock->getBindingsModule())
            ->send(Phake::capture($message), Phake::capture($metadata));
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:consent:inapplicable', $message->getConsent());
    }

    public function testConsentIsSkippedWhenDisabledPerSp()
    {
        $idp = $this->proxyServerMock->getRepository()->fetchIdentityProviderByEntityId('testIdP');

        $idp->setConsentSettings(
            new ConsentSettings([
                [
                    'name' => 'testSp',
                    'type' => 'no_consent',
                ]
            ])
        );

        $provideConsentService = $this->factoryService();

        $provideConsentService->serve(null, $this->httpRequestMock);

        Phake::verify($this->proxyServerMock->getBindingsModule())
            ->send(Phake::capture($message), Phake::capture($metadata));
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:consent:inapplicable', $message->getConsent());
    }

    /**
     * @return EngineBlock_Corto_ProxyServer
     */
    private function mockProxyServer()
    {
        // Mock twig, a dependency of proxy server
        $twigMock = Phake::mock(Twig_Environment::class);
        // Mock proxy server
        /** @var EngineBlock_Corto_ProxyServer $proxyServerMock */
        $proxyServerMock = Phake::partialMock('EngineBlock_Corto_ProxyServer', $twigMock);

        $proxyServerMock->setRepository(new InMemoryMetadataRepository(
            array(new IdentityProvider('testIdP')),
            array(new ServiceProvider('testSp'))
        ));

        $bindingsModuleMock = $this->mockBindingsModule();
        $proxyServerMock->setBindingsModule($bindingsModuleMock);

        Phake::when($proxyServerMock)
            ->renderTemplate(Phake::anyParameters())
            ->thenReturn(null);

        Phake::when($proxyServerMock)
            ->sendOutput(Phake::anyParameters())
            ->thenReturn(null);

        return $proxyServerMock;
    }

    /**
     * @return EngineBlock_Corto_Module_Bindings
     */
    private function mockBindingsModule()
    {
        $spRequest = new AuthnRequest();
        $spRequest->setId('SPREQUEST');
        $issuer = new Issuer();
        $issuer->setValue('testSp');
        $spRequest->setIssuer($issuer);
        $spRequest = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($spRequest);

        $ebRequest = new AuthnRequest();
        $ebRequest->setId('EBREQUEST');
        $ebRequest = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($ebRequest);

        $dummyLog = new Psr\Log\NullLogger();
        $authnRequestRepository = new EngineBlock_Saml2_AuthnRequestSessionRepository($dummyLog);
        $authnRequestRepository->store($spRequest);
        $authnRequestRepository->store($ebRequest);
        $authnRequestRepository->link($ebRequest, $spRequest);

        $assertion = new Assertion();
        $assertion->setAttributes(array(
            'urn:org:openconext:corto:internal:sp-entity-id' => array(
                'testSp'
            ),
            'urn:mace:dir:attribute-def:cn' => array(
                null
            )
        ));
        $nameId = new NameID();
        $nameId->setValue('nameid');
        $assertion->setNameId($nameId);

        $responseFixture = new Response();
        $responseFixture->setInResponseTo('EBREQUEST');
        $responseFixture->setAssertions(array($assertion));
        $responseFixture = new EngineBlock_Saml2_ResponseAnnotationDecorator($responseFixture);
        $responseFixture->setOriginalIssuer('testIdP');

        // Mock bindings module
        /** @var EngineBlock_Corto_Module_Bindings $bindingsModuleMock */
        $bindingsModuleMock = Phake::mock('EngineBlock_Corto_Module_Bindings');
        Phake::when($bindingsModuleMock)
            ->receiveResponse(Phake::anyParameters())
            ->thenReturn($responseFixture);

        return $bindingsModuleMock;
    }

    /**
     * @param EngineBlock_Corto_XmlToArray $xmlConverterMock
     * @return EngineBlock_Corto_XmlToArray
     */
    private function mockXmlConverter(EngineBlock_Corto_XmlToArray $xmlConverterMock)
    {
        // Mock xml conversion
        $xmlFixture = array(
            'urn:org:openconext:corto:internal:sp-entity-id' => array(
                'testSp'
            ),
            'urn:mace:dir:attribute-def:cn' => array(
                null
            )
        );
        Phake::when($xmlConverterMock)
            ->attributesToArray(Phake::anyParameters())
            ->thenReturn($xmlFixture);

        return $xmlConverterMock;
    }

    /**
     * @param EngineBlock_Corto_Model_Consent_Factory $this->consentFactoryMock
     * @return EngineBlock_Corto_Model_Consent
     */
    private function mockConsent()
    {
        $consentMock = Phake::mock('EngineBlock_Corto_Model_Consent');
        Phake::when($consentMock)
            ->explicitConsentWasGivenFor(Phake::anyParameters())
            ->thenReturn(false);
        Phake::when($this->consentFactoryMock)
            ->create(Phake::anyParameters())
            ->thenReturn($consentMock);

        return $consentMock;
    }

    /**
     * @return ConsentService
     */
    private function mockConsentService()
    {
        $mock = Phake::mock(ConsentServiceInterface::class);
        Phake::when($mock)
            ->countAllFor(Phake::anyParameters())
            ->thenReturn(3);

        return $mock;
    }

    private function mockAuthStateHelper()
    {
        $authState = Phake::mock(AuthenticationStateInterface::class);
        Phake::when($authState)
            ->completeCurrentProcedure('_00000000-0000-0000-0000-000000000000');

        $mock = Phake::mock(AuthenticationStateHelperInterface::class);
        Phake::when($mock)
            ->getAuthenticationState()
            ->thenReturn($authState);

        return $mock;
    }

    private function mockTwig()
    {
        $mock = Phake::mock(\Twig\Environment::class);
        Phake::when($mock)
            ->render(Phake::anyParameters())
            ->thenReturn('');

        return $mock;
    }

    /**
     * @return EngineBlock_Corto_Module_Service_ProvideConsent
     */
    private function factoryService()
    {
        return new EngineBlock_Corto_Module_Service_ProvideConsent(
            $this->proxyServerMock,
            $this->xmlConverterMock,
            $this->consentFactoryMock,
            $this->consentService,
            $this->authStateHelperMock,
            $this->twig,
            $this->processingStateHelperMock
        );
    }

    private function mockSspResponse()
    {
        $sspResponse = new Response();
        $sspResponse->setInResponseTo('EBREQUEST');
        $issuer = new Issuer();
        $issuer->setValue('testSp');
        $sspResponse->setIssuer($issuer);
        $sspResponse->setAssertions([]);

        $sspResponse = new EngineBlock_Saml2_ResponseAnnotationDecorator($sspResponse);

        return $sspResponse;
    }

    private function mockProcessingStateHelper()
    {
        $this->sessionMock = new MockArraySessionStorage();

        $sessionData = [
            'Processing' => [
                'SPREQUEST' => [
                    'consent' => new ProcessingStateStep($this->sspResponseMock, new ServiceProvider('https://sp.example.edu')),
                ],
            ],
        ];

        $this->sessionMock->setSessionData(['_sf2_attributes' => $sessionData]);

        $session = new Session($this->sessionMock);
        return new ProcessingStateHelper($session);
    }

    private function mockHttpRequest()
    {
        $helperMock = Phake::mock(Request::class);

        return $helperMock;
    }

    private function setCoin(ServiceProvider $sp, $key, $name)
    {
        $jsonData = $sp->getCoins()->toJson();
        $data = json_decode($jsonData, true);
        $data[$key] = $name;
        $jsonData = json_encode($data);

        $coins = Coins::fromJson($jsonData);

        $object = new \ReflectionClass($sp);

        $property = $object->getProperty('coins');
        $property->setAccessible(true);
        $property->setValue($sp, $coins);
    }
}
