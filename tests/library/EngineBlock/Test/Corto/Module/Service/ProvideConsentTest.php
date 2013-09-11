<?php

class EngineBlock_Test_Corto_Module_Service_ProvideConsentTest extends PHPUnit_Framework_TestCase
{
    /** @var EngineBlock_Corto_XmlToArray */
    private $xmlConverterMock;

    /** @var EngineBlock_Corto_Model_Consent_Factory */
    private $consentFactoryMock;

    /** @var EngineBlock_Corto_Model_Consent */
    private $consentMock;

    public function setup() {
        EngineBlock_ApplicationSingleton::getInstance()->bootstrap();

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
            ->send(Phake::capture($message), Phake::anyParameters());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:consent:prior', $message['_Consent']);
    }

    public function testConsentIsSkippedWhenGloballyDisabled()
    {
        $provideConsentService = $this->factoryService();

        Phake::when($this->proxyServerMock)
            ->getRemoteEntity('testSp')
            ->thenReturn(
                array(
                    'NoConsentRequired' => true
                )
            );

        $provideConsentService->serve(null);

        Phake::verify($this->proxyServerMock->getBindingsModule())
            ->send(Phake::capture($message), Phake::anyParameters());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:consent:inapplicable', $message['_Consent']);
    }

    public function testConsentIsSkippedWhenDisabledPerSp()
    {
        $provideConsentService = $this->factoryService();

        Phake::when($this->proxyServerMock)
            ->getRemoteEntity('testIdP')
            ->thenReturn(
                array(
                    'SpsWithoutConsent' => array(
                        'testSp'
                    )
                )
            );

        $provideConsentService->serve(null);

        Phake::verify($this->proxyServerMock->getBindingsModule())
            ->send(Phake::capture($message), Phake::anyParameters());
        $this->assertEquals('urn:oasis:names:tc:SAML:2.0:consent:inapplicable', $message['_Consent']);
    }

    public function testFilteredAttributesAreUsedToRenderTemplate()
    {
        $provideConsentService = $this->factoryService();

        $expectedFilteredAttributes = array('foo');
        Phake::when($this->consentMock)
            ->getFilteredResponseAttributes(Phake::anyParameters())
            ->thenReturn($expectedFilteredAttributes);

        $provideConsentService->serve(null);

        Phake::verify($this->proxyServerMock)
            ->renderTemplate(Phake::capture($templateName), Phake::capture($vars));

        $this->assertEquals($expectedFilteredAttributes, $vars['attributes']);
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
