<?php

class EngineBlock_Corto_Module_Service_ProccessConsentTest extends PHPUnit_Framework_TestCase
{
    /** @var EngineBlock_Corto_XmlToArray */
    private $xmlConverterMock;

    /** @var EngineBlock_Corto_Model_Consent_Factory */
    private $consentFactoryMock;

    /** @var EngineBlock_Mail_Mailer */
    private $mailerMock;

    public function setup() {
        EngineBlock_ApplicationSingleton::getInstance()->bootstrap();
        $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $this->xmlConverterMock = $diContainer[EngineBlock_Application_DiContainer::XML_CONVERTER];
        $this->consentFactoryMock = $diContainer[EngineBlock_Application_DiContainer::CONSENT_FACTORY];
        $this->mailerMock = $diContainer[EngineBlock_Application_DiContainer::MAILER];
    }

    /**
     * @expectedException EngineBlock_Corto_Module_Services_SessionLostException
     * @expectedExceptionMessage Session lost after consent
     */
    public function testSessionLostExceptionIfNoSession()
    {
        $proxyServerMock = $this->mockProxyServer();
        $this->mockXmlConverterResponse();

        $this->mockGlobals();
        unset($_SESSION['consent']);

        $processConsentService = new EngineBlock_Corto_Module_Service_ProcessConsent(
            $proxyServerMock,
            $this->xmlConverterMock,
            $this->consentFactoryMock,
            $this->mailerMock
        );
        $processConsentService->serve(null);
    }

    /**
     * @expectedException EngineBlock_Corto_Module_Services_SessionLostException
     * @expectedExceptionMessage Stored response for ResponseID 'test' not found
     */
    public function testSessionLostExceptionIfPostIdNotInSession()
    {
        $proxyServerMock = $this->mockProxyServer();
        $this->mockXmlConverterResponse();

        $this->mockGlobals();
        unset($_SESSION['consent']['test']);

        $processConsentService = new EngineBlock_Corto_Module_Service_ProcessConsent(
            $proxyServerMock,
            $this->xmlConverterMock,
            $this->consentFactoryMock,
            $this->mailerMock
        );
        $processConsentService->serve(null);
    }

    public function testRedirectToFeedbackPageIfConsentNotInPost() {
        $proxyServerMock = $this->mockProxyServer();
        Phake::when($proxyServerMock)
            ->redirect(Phake::anyParameters())
            ->thenReturn(null);

        $this->mockXmlConverterResponse();

        $this->mockGlobals();
        unset($_POST['consent']);

        $processConsentService = new EngineBlock_Corto_Module_Service_ProcessConsent(
            $proxyServerMock,
            $this->xmlConverterMock,
            $this->consentFactoryMock,
            $this->mailerMock
        );
        $processConsentService->serve(null);

        Phake::verify(($proxyServerMock))->redirect(Phake::anyParameters());
    }

    public function testConsentIsStored()
    {
        $proxyServerMock = $this->mockProxyServer();
        $this->mockXmlConverterResponse();

        $this->mockGlobals();

        $processConsentService = new EngineBlock_Corto_Module_Service_ProcessConsent(
            $proxyServerMock,
            $this->xmlConverterMock,
            $this->consentFactoryMock,
            $this->mailerMock
        );

        $consentMock = $this->mockConsent();
        Phake::when($consentMock)
            ->storeConsent(Phake::anyParameters())
            ->thenReturn(true);

        $processConsentService->serve(null);

        Phake::verify(($consentMock))->storeConsent(Phake::anyParameters());
    }

    public function testIntroductionMailIsSentOnFirstConsentIfEmailIsKnown() {
        $proxyServerMock = $this->mockProxyServer();
        $this->mockXmlConverterResponse();

        $this->mockGlobals();

        $consentMock = $this->mockConsent();
        Phake::when($consentMock)
            ->countTotalConsent(Phake::anyParameters())
            ->thenReturn(1);

        $configurationMock = new stdClass();
        $configurationMock->email = new stdClass();
        $configurationMock->email->sendWelcomeMail = true;

        $processConsentService = new EngineBlock_Corto_Module_Service_ProcessConsent(
            $proxyServerMock,
            $this->xmlConverterMock,
            $this->consentFactoryMock,
            $this->mailerMock
        );

        $processConsentService->serve(null);

        Phake::verify($this->mailerMock)->sendMail(Phake::anyParameters());
    }

    public function testResponseIsSent() {
        $proxyServerMock = $this->mockProxyServer();
        Phake::when($proxyServerMock)
            ->redirect(Phake::anyParameters())
            ->thenReturn(null);

        Phake::when($proxyServerMock->getBindingsModule())
            ->send(Phake::anyParameters())
            ->thenReturn(null);

        $this->mockXmlConverterResponse();

        $this->mockGlobals();

        $processConsentService = new EngineBlock_Corto_Module_Service_ProcessConsent(
            $proxyServerMock,
            $this->xmlConverterMock,
            $this->consentFactoryMock,
            $this->mailerMock
        );
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
    private function mockXmlConverterResponse()
    {
        // Mock xml conversion
        $xmlFixture = array(
            'urn:mace:dir:attribute-def:mail' => 'test@test.test'
        );
        Phake::when($this->xmlConverterMock)
            ->attributesToArray(Phake::anyParameters())
            ->thenReturn($xmlFixture);
    }

    private function mockGlobals()
    {
        $_POST['ID'] = 'test';
        $_POST['consent'] = 'yes';
        $_SESSION['consent']['test']['response']['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute'] = array();
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
}
