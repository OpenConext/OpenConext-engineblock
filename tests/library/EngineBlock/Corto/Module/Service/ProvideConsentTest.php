<?php

class EngineBlock_Corto_Module_Service_ProvideConsentTest extends PHPUnit_Framework_TestCase
{
    /** @var EngineBlock_Corto_XmlToArray */
    private $xmlConverterMock;

    /** @var EngineBlock_Corto_Model_Consent_Factory */
    private $consentFactoryMock;

    public function setup() {
        EngineBlock_ApplicationSingleton::getInstance()->bootstrap();
        $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $this->xmlConverterMock = $diContainer[EngineBlock_Application_DiContainer::XML_CONVERTER];
        $this->consentFactoryMock = $diContainer[EngineBlock_Application_DiContainer::CONSENT_FACTORY];
    }

    public function testConsentRequested()
    {
        $proxyServerMock = $this->mockProxyServer();

        $this->mockXmlConverterResponse();
        $provideConsentService = new EngineBlock_Corto_Module_Service_ProvideConsent(
            $proxyServerMock,
            $this->xmlConverterMock,
            $this->consentFactoryMock
        );
        $this->mockConsent();

        Phake::when($proxyServerMock)
            ->renderTemplate(Phake::anyParameters())
            ->thenReturn(null);

        Phake::when($proxyServerMock)
            ->sendOutput(Phake::anyParameters())
            ->thenReturn(null);

        $provideConsentService->serve('idpMetadata');

        Phake::verify($proxyServerMock)
            ->renderTemplate(Phake::anyParameters());
    }

    public function testConsentIsSkippedWhenPriorConsentIsStored()
    {
        $proxyServerMock = $this->mockProxyServer();

        $this->mockXmlConverterResponse();
        $provideConsentService = new EngineBlock_Corto_Module_Service_ProvideConsent(
            $proxyServerMock,
            $this->xmlConverterMock,
            $this->consentFactoryMock
        );
        $consentMock = $this->mockConsent();

        Phake::when($consentMock)
            ->hasStoredConsent(Phake::anyParameters())
            ->thenReturn(true);

        $provideConsentService->serve('idpMetadata');

        Phake::verify($proxyServerMock->getBindingsModule())
            ->send(Phake::capture($message), Phake::anyParameters());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:consent:prior', $message['_Consent']);
    }

    public function testConsentIsSkippedWhenGloballyDisabled()
    {
        $proxyServerMock = $this->mockProxyServer();

        $this->mockXmlConverterResponse();
        $provideConsentService = new EngineBlock_Corto_Module_Service_ProvideConsent(
            $proxyServerMock,
            $this->xmlConverterMock,
            $this->consentFactoryMock
        );
        $this->mockConsent();

        Phake::when($proxyServerMock)
            ->getRemoteEntity('testSp')
            ->thenReturn(
                array(
                    'NoConsentRequired' => true
                )
            );

        $provideConsentService->serve('idpMetadata');

        Phake::verify($proxyServerMock->getBindingsModule())
            ->send(Phake::capture($message), Phake::anyParameters());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:consent:inapplicable', $message['_Consent']);
    }

    public function testConsentIsSkippedWhenDisabledPerSp()
    {
        $proxyServerMock = $this->mockProxyServer();

        $this->mockXmlConverterResponse();
        $provideConsentService = new EngineBlock_Corto_Module_Service_ProvideConsent(
            $proxyServerMock,
            $this->xmlConverterMock,
            $this->consentFactoryMock
        );
        $this->mockConsent($provideConsentService);

        Phake::when($proxyServerMock)
            ->getRemoteEntity('testIdP')
            ->thenReturn(
                array(
                    'SpsWithoutConsent' => array(
                        'testSp'
                    )
                )
            );

        $provideConsentService->serve('idpMetadata');

        Phake::verify($proxyServerMock->getBindingsModule())
            ->send(Phake::capture($message), Phake::anyParameters());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:consent:inapplicable', $message['_Consent']);
    }

    /**
     * @return EngineBlock_Corto_ProxyServer
     */
    private function mockProxyServer()
    {
        // Mock proxy server
        $_SERVER['HTTP_HOST'] = 'test-host';
        $proxyServerMock = Phake::partialMock('EngineBlock_Corto_ProxyServer');

        Phake::when($proxyServerMock)
            ->getRemoteEntity('testSp')
            ->thenReturn(array());
        Phake::when($proxyServerMock)
            ->getRemoteEntity('testIdP')
            ->thenReturn(array());

        $bindingsModuleMock = $this->mockBindingsModule();
        $proxyServerMock->setBindingsModule($bindingsModuleMock);

        return $proxyServerMock;
    }

    /**
     * @return EngineBlock_Corto_Module_Bindings
     */
    private function mockBindingsModule()
    {
        // Mock bindings module
        $bindingsModuleMock = Phake::mock('EngineBlock_Corto_Module_Bindings');
        $responseFixture = array(
            '_ID' => null,
            'saml:Assertion' => array(
                'saml:AttributeStatement' => array(
                    array(
                        'saml:Attribute' => array()
                    )
                )
            ),
            '__' => array(
                'OriginalIssuer' => 'testIdP'
            )
        );
        Phake::when($bindingsModuleMock)
            ->receiveResponse()
            ->thenReturn($responseFixture);

        return $bindingsModuleMock;
    }

    /**
     * @return EngineBlock_Corto_XmlToArray
     */
    private function mockXmlConverterResponse()
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
        Phake::when($this->xmlConverterMock)
            ->attributesToArray(Phake::anyParameters())
            ->thenReturn($xmlFixture);
    }

    /**
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
}
