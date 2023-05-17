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
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\InMemoryMetadataRepository;
use OpenConext\EngineBlock\Service\AuthenticationStateHelperInterface;
use OpenConext\EngineBlock\Service\Dto\ProcessingStateStep;
use OpenConext\EngineBlock\Service\ProcessingStateHelper;
use OpenConext\EngineBlock\Service\ProcessingStateHelperInterface;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationStateInterface;
use PHPUnit\Framework\TestCase;
use SAML2\Assertion;
use SAML2\AuthnRequest;
use SAML2\Response;
use SAML2\XML\saml\Issuer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class EngineBlock_Test_Corto_Module_Service_ProcessConsentTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var EngineBlock_Corto_ProxyServer
     */
    private $proxyServerMock;

    /**
     * @var EngineBlock_Corto_XmlToArray
     */
    private $xmlConverterMock;

    /**
     * @var EngineBlock_Corto_Model_Consent_Factory
     */
    private $consentFactoryMock;

    /**
     * @var AuthenticationStateHelperInterface
     */
    private $authnStateHelperMock;

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

        $this->proxyServerMock    = $this->mockProxyServer();
        $this->xmlConverterMock   = $this->mockXmlConverter($diContainer->getXmlConverter());
        $this->consentFactoryMock = $diContainer->getConsentFactory();
        $this->authnStateHelperMock = $this->mockAuthnStateHelper();
        $this->sspResponseMock = $this->mockSspResponse();
        $this->processingStateHelperMock = $this->mockProcessingStateHelper();
        $this->httpRequestMock = $this->mockHttpRequest();
    }

    public function testSessionLostExceptionIfNoSession()
    {
        $this->expectException(EngineBlock_Corto_Module_Services_SessionLostException::class);
        $this->expectExceptionMessage('Session lost after consent');

        $sessionData = [
            'Processing' => [],
        ];
        $this->sessionMock->setSessionData(['_sf2_attributes' => $sessionData]);

        $processConsentService = $this->factoryService();

        $processConsentService->serve(null, $this->httpRequestMock);
    }

    public function testSessionLostExceptionIfPostIdNotInSession()
    {
        $this->expectException(EngineBlock_Corto_Module_Services_SessionLostException::class);
        $this->expectExceptionMessage('Stored response for ResponseID "test" not found');

        $sessionData = [
            'Processing' => [
                'unknown' => null,
            ],
        ];
        $this->sessionMock->setSessionData(['_sf2_attributes' => $sessionData]);

        $processConsentService = $this->factoryService();
        $processConsentService->serve(null, $this->httpRequestMock);
    }

    public function testRedirectToFeedbackPageIfConsentNotInPost()
    {
        $this->expectException(EngineBlock_Corto_Exception_NoConsentProvided::class);

        $processConsentService = $this->factoryService();

        $processConsentService->serve(null, $this->mockHttpRequestNoConsent());
    }

    public function testConsentIsStored()
    {
        $processConsentService = $this->factoryService();

        $consentMock = $this->mockConsent();
        Phake::when($consentMock)
            ->giveExplicitConsentFor(Phake::anyParameters())
            ->thenReturn(true);

        $processConsentService->serve(null, $this->httpRequestMock);

        Phake::verify(($consentMock))->giveExplicitConsentFor(Phake::anyParameters());
    }

    public function testResponseIsSent() {
        $processConsentService = $this->factoryService();

        Phake::when($this->proxyServerMock)
            ->redirect(Phake::anyParameters())
            ->thenReturn(null);

        Phake::when($this->proxyServerMock->getBindingsModule())
            ->send(Phake::anyParameters())
            ->thenReturn(null);

        $processConsentService->serve(null, $this->httpRequestMock);

        Phake::verify(($this->proxyServerMock->getBindingsModule()))->send(Phake::anyParameters());
    }

    /**
     * @return EngineBlock_Corto_ProxyServer
     */
    private function mockProxyServer()
    {
        // Mock twig, a dependency of proxy server
        $twigMock = Phake::mock(Twig_Environment::class);

        // Mock proxy server
        $_SERVER['HTTP_HOST'] = 'test-host';
        /** @var EngineBlock_Corto_ProxyServer $proxyServerMock */
        $proxyServerMock = Phake::partialMock('EngineBlock_Corto_ProxyServer', $twigMock);
        $proxyServerMock
            ->setRepository(new InMemoryMetadataRepository(
                array(),
                array(new ServiceProvider('https://sp.example.edu'))
            ))
            ->setBindingsModule($this->mockBindingsModule());

        return $proxyServerMock;
    }

    /**
     * @return EngineBlock_Corto_Module_Bindings
     */
    private function mockBindingsModule()
    {
        // Mock bindings module
        $bindingsModuleMock = Phake::mock('EngineBlock_Corto_Module_Bindings');

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
            'urn:mace:dir:attribute-def:mail' => 'test@test.test'
        );
        Phake::when($xmlConverterMock)
            ->attributesToArray(Phake::anyParameters())
            ->thenReturn($xmlFixture);

        return $xmlConverterMock;
    }

    /**
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

    private function mockAuthnStateHelper()
    {
        $authStateMock = Phake::mock(AuthenticationStateInterface::class);

        $helperMock = Phake::mock(AuthenticationStateHelperInterface::class);
        Phake::when($helperMock)
            ->getAuthenticationState()
            ->thenReturn($authStateMock);

        return $helperMock;
    }

    private function mockSspResponse()
    {
        $assertion = new Assertion();
        $assertion->setAttributes(array(
            'urn:mace:dir:attribute-def:mail' => 'test@test.test'
        ));

        $spRequest = new AuthnRequest();
        $spRequest->setId('SPREQUEST');
        $issuer = new Issuer();
        $issuer->setValue('https://sp.example.edu');
        $spRequest->setIssuer($issuer);
        $spRequest = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($spRequest);

        $ebRequest = new AuthnRequest();
        $ebRequest->setId('EBREQUEST');
        $ebRequest = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($ebRequest);

        $dummySessionLog = new Psr\Log\NullLogger();
        $authnRequestRepository = new EngineBlock_Saml2_AuthnRequestSessionRepository($dummySessionLog);
        $authnRequestRepository->store($spRequest);
        $authnRequestRepository->store($ebRequest);
        $authnRequestRepository->link($ebRequest, $spRequest);

        $sspResponse = new Response();
        $sspResponse->setInResponseTo('EBREQUEST');
        $sspResponse->setAssertions(array($assertion));

        $sspResponse = new EngineBlock_Saml2_ResponseAnnotationDecorator($sspResponse);
        $sspResponse->setOriginalIssuer("https://idp.example.edu");

        return $sspResponse;
    }

    private function mockProcessingStateHelper()
    {
        $this->sessionMock = new MockArraySessionStorage();

        $sessionData = [
            'Processing' => [
                'test' => [
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
        Phake::when($helperMock)
            ->get('ID')
            ->thenReturn('test')
        ;

        Phake::when($helperMock)
            ->get('consent', 'no')
            ->thenReturn('yes');

        return $helperMock;
    }

    private function mockHttpRequestNoConsent()
    {
        $helperMock = Phake::mock(Request::class);
        Phake::when($helperMock)
            ->get('ID')
            ->thenReturn('test')
        ;

        Phake::when($helperMock)
            ->get('consent', 'no')
            ->thenReturn('no');

        return $helperMock;
    }

    /**
     * @return EngineBlock_Corto_Module_Service_ProcessConsent
     */
    private function factoryService()
    {
        return new EngineBlock_Corto_Module_Service_ProcessConsent(
            $this->proxyServerMock,
            $this->xmlConverterMock,
            $this->consentFactoryMock,
            $this->authnStateHelperMock,
            $this->processingStateHelperMock
        );
    }
}
