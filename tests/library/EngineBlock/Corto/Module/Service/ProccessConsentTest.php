<?php

class EngineBlock_Corto_Module_Service_ProccessConsentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EngineBlock_Application_DiContainer
     */
    private $diContainer;

    /** @var EngineBlock_Corto_Model_Consent */
    private $consent;

    public function setup() {
        EngineBlock_ApplicationSingleton::getInstance()->bootstrap();
        $this->diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
    }


    /**
     * @expectedException EngineBlock_Corto_Module_Services_SessionLostException
     * @expectedExceptionMessage Session lost after consent
     */
    public function testSessionLostExceptionIfNoSession()
    {
        $proxyServerMock = $this->mockProxyServer();
        $xmlConverterMock = $this->mockXmlConverter();

        $this->mockGlobals();
        unset($_SESSION['consent']);

        $provideConsentService = new EngineBlock_Corto_Module_Service_ProcessConsent($proxyServerMock, $xmlConverterMock);
        $provideConsentService->serve(null);
    }

    /**
     * @expectedException EngineBlock_Corto_Module_Services_SessionLostException
     * @expectedExceptionMessage Stored response for ResponseID 'test' not found
     */
    public function testSessionLostExceptionIfPostIdNotInSession()
    {
        $proxyServerMock = $this->mockProxyServer();
        $xmlConverterMock = $this->mockXmlConverter();

        $this->mockGlobals();
        unset($_SESSION['consent']['test']);

        $provideConsentService = new EngineBlock_Corto_Module_Service_ProcessConsent($proxyServerMock, $xmlConverterMock);
        $provideConsentService->serve(null);
    }

    public function testRedirectToFeedbackPageIfConsentNotInPost() {
        $proxyServerMock = $this->mockProxyServer();
        Phake::when($proxyServerMock)
            ->redirect(Phake::anyParameters())
            ->thenReturn(null);

        $xmlConverterMock = $this->mockXmlConverter();

        $this->mockGlobals();
        unset($_POST['consent']);

        $provideConsentService = new EngineBlock_Corto_Module_Service_ProcessConsent($proxyServerMock, $xmlConverterMock);
        $provideConsentService->serve(null);

        Phake::verify(($proxyServerMock))->redirect(Phake::anyParameters());
    }

    // @todo test response is sent

    // @Todo test consent is stored

    /**
     * @return EngineBlock_Corto_ProxyServer
     */
    private function mockProxyServer()
    {
        // Mock proxy server
        $_SERVER['HTTP_HOST'] = 'test-host';
        $proxyServerMock = Phake::partialMock('EngineBlock_Corto_ProxyServer');

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
        $xmlFixture = array();
        Phake::when($xmlConverter)
            ->attributesToArray(Phake::anyParameters())
            ->thenReturn($xmlFixture);

        return $xmlConverter;
    }

    private function mockGlobals()
    {
        $_POST['ID'] = 'test';
        $_POST['consent'] = 'yes';
        $_SESSION['consent']['test']['response']['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute'] = array();
    }
}
