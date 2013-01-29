<?php

class EngineBlock_Corto_Module_Service_ProvideConsentTest extends PHPUnit_Framework_TestCase
{
    public function setup() {
        EngineBlock_ApplicationSingleton::getInstance()->bootstrap();
    }

    public function testServe()
    {
        $proxyServerMock = $this->mockProxyServer();

        $bindingsModuleMock = $this->mockBindingsModule();
        $proxyServerMock->setBindingsModule($bindingsModuleMock);

        $provideConsentService = new EngineBlock_Corto_Module_Service_ProvideConsent($proxyServerMock);
        Phake::when($provideConsentService->consentFactory)->hasStoredConsent()->thenReturn(false);
        $this->mockXmlConverter($provideConsentService->xmlConverter);

        $provideConsentService->serve('idpMetadata');
    }

    /**
     * @return Phake_IMock
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
     * @return Phake_IMock
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
     * @param EngineBlock_Corto_Module_Service_ProvideConsent $provideConsentService
     */
    private function mockXmlConverter(EngineBlock_Corto_XmlToArray $xmlConverter)
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
        Phake::when($xmlConverter)
            ->attributesToArray(Phake::anyParameters())
            ->thenReturn($xmlFixture);
    }
}
