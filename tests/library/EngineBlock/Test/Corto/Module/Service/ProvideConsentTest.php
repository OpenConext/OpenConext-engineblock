<?php

use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\InMemoryMetadataRepository;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;

class EngineBlock_Test_Corto_Module_Service_ProvideConsentTest extends PHPUnit_Framework_TestCase
{
    /** @var EngineBlock_Corto_XmlToArray */
    private $xmlConverterMock;

    /** @var EngineBlock_Corto_Model_Consent_Factory */
    private $consentFactoryMock;

    /** @var EngineBlock_Corto_Model_Consent */
    private $consentMock;

    /** @var  EngineBlock_Corto_ProxyServer */
    private $proxyServerMock;

    public function setup() {
        $this->proxyServerMock = $this->mockProxyServer();

        $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $this->xmlConverterMock = $this->mockXmlConverter($diContainer[EngineBlock_Application_DiContainer::XML_CONVERTER]);
        $this->consentFactoryMock = $diContainer[EngineBlock_Application_DiContainer::CONSENT_FACTORY];
        $this->consentMock = $this->mockConsent();
    }

    public function testConsentRequested()
    {
        $provideConsentService = $this->factoryService();

        $provideConsentService->serve(null);

        Phake::verify($this->proxyServerMock)
            ->renderTemplate(Phake::anyParameters());
    }

    public function testConsentIsSkippedWhenPriorConsentIsStored()
    {
        $provideConsentService = $this->factoryService();

        Phake::when($this->consentMock)
            ->hasStoredConsent(Phake::anyParameters())
            ->thenReturn(true);

        $provideConsentService->serve(null);

        Phake::verify($this->proxyServerMock->getBindingsModule())
            ->send(Phake::capture($message), Phake::capture($metadata));
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:consent:prior', $message->getConsent());
    }

    public function testConsentIsSkippedWhenGloballyDisabled()
    {
        $this->proxyServerMock->getRepository()->fetchServiceProviderByEntityId('testSp')->isConsentRequired = false;

        $provideConsentService = $this->factoryService();

        $provideConsentService->serve(null);

        Phake::verify($this->proxyServerMock->getBindingsModule())
            ->send(Phake::capture($message), Phake::capture($metadata));
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:consent:inapplicable', $message->getConsent());
    }

    public function testConsentIsSkippedWhenDisabledPerSp()
    {
        $this->proxyServerMock->getRepository()->fetchIdentityProviderByEntityId('testIdP')->spsEntityIdsWithoutConsent[] = 'testSp';

        $provideConsentService = $this->factoryService();

        $provideConsentService->serve(null);

        Phake::verify($this->proxyServerMock->getBindingsModule())
            ->send(Phake::capture($message), Phake::capture($metadata));
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:consent:inapplicable', $message->getConsent());
    }

    /**
     * @return EngineBlock_Corto_ProxyServer
     */
    private function mockProxyServer()
    {
        // Mock proxy server
        /** @var EngineBlock_Corto_ProxyServer $proxyServerMock */
        $proxyServerMock = Phake::partialMock('EngineBlock_Corto_ProxyServer');
        $proxyServerMock->setHostname('test-host');

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
        $spRequest = new SAML2_AuthnRequest();
        $spRequest->setId('SPREQUEST');
        $spRequest->setIssuer('testSp');
        $spRequest = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($spRequest);

        $ebRequest = new SAML2_AuthnRequest();
        $ebRequest->setId('EBREQUEST');
        $ebRequest = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($ebRequest);
        
        $dummyLog = new Psr\Log\NullLogger();
        $authnRequestRepository = new EngineBlock_Saml2_AuthnRequestSessionRepository($dummyLog);
        $authnRequestRepository->store($spRequest);
        $authnRequestRepository->store($ebRequest);
        $authnRequestRepository->link($ebRequest, $spRequest);

        $assertion = new SAML2_Assertion();
        $assertion->setAttributes(array(
            'urn:org:openconext:corto:internal:sp-entity-id' => array(
                'testSp'
            ),
            'urn:mace:dir:attribute-def:cn' => array(
                null
            )
        ));

        $responseFixture = new SAML2_Response();
        $responseFixture->setInResponseTo('EBREQUEST');
        $responseFixture->setAssertions(array($assertion));
        $responseFixture = new EngineBlock_Saml2_ResponseAnnotationDecorator($responseFixture);
        $responseFixture->setOriginalIssuer('testIdP');

        // Mock bindings module
        /** @var EngineBlock_Corto_Module_Bindings $bindingsModuleMock */
        $bindingsModuleMock = Phake::mock('EngineBlock_Corto_Module_Bindings');
        Phake::when($bindingsModuleMock)
            ->receiveResponse()
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
            ->hasStoredConsent(Phake::anyParameters())
            ->thenReturn(false);
        Phake::when($this->consentFactoryMock)
            ->create(Phake::anyParameters())
            ->thenReturn($consentMock);

        return $consentMock;
    }

    /**
     * @return EngineBlock_Corto_Module_Service_ProvideConsent
     */
    private function factoryService()
    {
        return new EngineBlock_Corto_Module_Service_ProvideConsent(
            $this->proxyServerMock,
            $this->xmlConverterMock,
            $this->consentFactoryMock
        );
    }
}
