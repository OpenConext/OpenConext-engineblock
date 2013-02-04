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

        $processConsentService = new EngineBlock_Corto_Module_Service_ProcessConsent($proxyServerMock, $xmlConverterMock);
        $processConsentService->serve(null);
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

        $processConsentService = new EngineBlock_Corto_Module_Service_ProcessConsent($proxyServerMock, $xmlConverterMock);
        $processConsentService->serve(null);
    }

    public function testRedirectToFeedbackPageIfConsentNotInPost() {
        $proxyServerMock = $this->mockProxyServer();
        Phake::when($proxyServerMock)
            ->redirect(Phake::anyParameters())
            ->thenReturn(null);

        $xmlConverterMock = $this->mockXmlConverter();

        $this->mockGlobals();
        unset($_POST['consent']);

        $processConsentService = new EngineBlock_Corto_Module_Service_ProcessConsent($proxyServerMock, $xmlConverterMock);
        $processConsentService->serve(null);

        Phake::verify(($proxyServerMock))->redirect(Phake::anyParameters());
    }

    public function testConsentIsStored()
    {
        $proxyServerMock = $this->mockProxyServer();
        $xmlConverterMock = $this->mockXmlConverter();

        $this->mockGlobals();

        $processConsentService = new EngineBlock_Corto_Module_Service_ProcessConsent($proxyServerMock, $xmlConverterMock);

        $consentMock = $this->mockConsent($processConsentService);
        Phake::when($consentMock)
            ->storeConsent(Phake::anyParameters())
            ->thenReturn(true);

        $processConsentService->serve(null);

        Phake::verify(($consentMock))->storeConsent(Phake::anyParameters());
    }

    // @todo test introduction mail is sent

    public function testResponseIsSent() {
        $proxyServerMock = $this->mockProxyServer();
        Phake::when($proxyServerMock)
            ->redirect(Phake::anyParameters())
            ->thenReturn(null);

        Phake::when($proxyServerMock->getBindingsModule())
            ->send(Phake::anyParameters())
            ->thenReturn(null);

        $xmlConverterMock = $this->mockXmlConverter();

        $this->mockGlobals();

        $processConsentService = new EngineBlock_Corto_Module_Service_ProcessConsent($proxyServerMock, $xmlConverterMock);
        $processConsentService->serve(null);

        Phake::verify(($proxyServerMock->getBindingsModule()))->send(Phake::anyParameters());
    }

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

        Phake::when($proxyServerMock)
            ->getRemoteEntity(Phake::anyParameters())
            ->thenReturn(array());

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

    /**
     * @param EngineBlock_Corto_Module_Service_ProvideConsent $processConsentService
     * @return EngineBlock_Corto_Model_Consent
     */
    private function mockConsent(EngineBlock_Corto_Module_Service_ProcessConsent $processConsentService)
    {
        $consentMock = Phake::mock('EngineBlock_Corto_Model_Consent');
        Phake::when($consentMock)
            ->hasStoredConsent(Phake::anyParameters())
            ->thenReturn(false);
        Phake::when($processConsentService->consentFactory)
            ->create(Phake::anyParameters())
            ->thenReturn($consentMock);

        return $consentMock;

    }
}
