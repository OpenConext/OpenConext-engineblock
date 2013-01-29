<?php

class EngineBlock_Corto_Module_Service_ProvideConsentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EngineBlock_Application_DiContainer
     */
    private $diContainer;

    public function setup() {
        EngineBlock_ApplicationSingleton::getInstance()->bootstrap();
        $this->diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
    }

    public function testServe()
    {
        $proxyServerMock = $this->mockProxyServer();

        $bindingsModuleMock = $this->mockBindingsModule();
        $proxyServerMock->setBindingsModule($bindingsModuleMock);

        $xmlConverterMock = $this->mockXmlConverter();
        $provideConsentService = new EngineBlock_Corto_Module_Service_ProvideConsent($proxyServerMock, $xmlConverterMock);
        Phake::when($provideConsentService->consentFactory)->hasStoredConsent()->thenReturn(false);

        $provideConsentService->serve('idpMetadata');
    }

    public function testConsentIsSkippedWhenGloballyDisabled()
    {
        $proxyServerMock = $this->mockProxyServer();

        $bindingsModuleMock = $this->mockBindingsModule();
        $proxyServerMock->setBindingsModule($bindingsModuleMock);

        $xmlConverterMock = $this->mockXmlConverter();
        $provideConsentService = new EngineBlock_Corto_Module_Service_ProvideConsent($proxyServerMock, $xmlConverterMock);
        Phake::when($provideConsentService->consentFactory)->hasStoredConsent()->thenReturn(false);

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

    public function testConsentIsSkippedWhenDisabledPerIdp()
    {
        $proxyServerMock = $this->mockProxyServer();

        $bindingsModuleMock = $this->mockBindingsModule();
        $proxyServerMock->setBindingsModule($bindingsModuleMock);

        $xmlConverterMock = $this->mockXmlConverter();
        $provideConsentService = new EngineBlock_Corto_Module_Service_ProvideConsent($proxyServerMock, $xmlConverterMock);
        Phake::when($provideConsentService->consentFactory)->hasStoredConsent()->thenReturn(false);

        Phake::when($proxyServerMock)
            ->getRemoteEntity('testSp')
            ->thenReturn(
                array(
                    'IdPsWithoutConsent' => array(
                        'testIdP'
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

        // @todo do not always mock this
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
     * @return EngineBlock_Corto_XmlToArray
     */
    private function mockXmlConverter()
    {
        /** @var $xmlConverter EngineBlock_Corto_XmlToArray */
        $xmlConverter = $this->diContainer[EngineBlock_Application_DiContainer::XML_CONVERTER];

        // Mock xml conversion
        $xmlFixture = array(
            'urn:org:openconext:corto:internal:sp-entity-id' => array(
                'testSp'
            ),
            'urn:mace:dir:attribute-def:cn' => array(
                null
            )
        );
        Phake::when($xmlConverter)
            ->attributesToArray(Phake::anyParameters())
            ->thenReturn($xmlFixture);

        return $xmlConverter;
    }
}
