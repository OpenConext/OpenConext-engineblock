<?php

class EngineBlock_Test_Corto_Module_Service_ProccessConsentTest extends PHPUnit_Framework_TestCase
{
    /** @var EngineBlock_Corto_ProxyServer */
    private $proxyServerMock;

    /** @var EngineBlock_Corto_XmlToArray */
    private $xmlConverterMock;

    /** @var EngineBlock_Corto_Model_Consent_Factory */
    private $consentFactoryMock;

    /** @var EngineBlock_Mail_Mailer */
    private $mailerMock;

    /** @var EngineBlock_Corto_Filter_Command_AttributeReleasePolicy */
    private $attributeReleasePolicyFilterMock;

    /** @var EngineBlock_Corto_Model_Consent_Repository */
    private $consentRepositoryMock;

    public function setup() {
        EngineBlock_ApplicationSingleton::getInstance()->bootstrap();

        $this->proxyServerMock = $this->mockProxyServer();

        $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $this->xmlConverterMock = $this->mockXmlConverter($diContainer[EngineBlock_Application_DiContainer::XML_CONVERTER]);
        $this->consentFactoryMock = $diContainer[EngineBlock_Application_DiContainer::CONSENT_FACTORY];
        $this->mailerMock = $diContainer[EngineBlock_Application_DiContainer::MAILER];
        $this->attributeReleasePolicyFilterMock = $this->mockAttributeReleasePolicyFilter();
        $this->consentRepositoryMock = $this->mockConsentRepository();
        $this->mockGlobals();
    }

    /**
     * @expectedException EngineBlock_Corto_Module_Services_SessionLostException
     * @expectedExceptionMessage Session lost after consent
     */
    public function testSessionLostExceptionIfNoSession()
    {
        $processConsentService = $this->factoryService();

        unset($_SESSION['consent']);

        $processConsentService->serve(null);
    }

    /**
     * @expectedException EngineBlock_Corto_Module_Services_SessionLostException
     * @expectedExceptionMessage Stored response for ResponseID 'test' not found
     */
    public function testSessionLostExceptionIfPostIdNotInSession()
    {
        unset($_SESSION['consent']['test']);

        $processConsentService = $this->factoryService();
        $processConsentService->serve(null);
    }

    /**
     * @expectedException EngineBlock_Corto_Exception_NoConsentProvided
     */
    public function testRedirectToFeedbackPageIfConsentNotInPost() {
        $processConsentService = $this->factoryService();

        unset($_POST['consent']);
        $processConsentService->serve(null);
    }

    public function testFilteredAttributesAreUsedToCreateConsent()
    {
        $expectedFilteredAttributes = array('foo');

        Phake::when($this->attributeReleasePolicyFilterMock)
            ->getResponseAttributes()
            ->thenReturn($expectedFilteredAttributes);

        $provideConsentService = $this->factoryService();
        $provideConsentService->serve(null);

        Phake::verify($this->consentFactoryMock)->create($this->proxyServerMock, 'testUser', null, $expectedFilteredAttributes);
    }

    public function testConsentIsStored()
    {
        $processConsentService = $this->factoryService();

        Phake::when($this->consentRepositoryMock)
            ->store(Phake::anyParameters())
            ->thenReturn(true);

        $processConsentService->serve(null);

        Phake::verify($this->consentRepositoryMock)->store(Phake::anyParameters());
    }

    public function testIntroductionMailIsSentOnFirstConsentIfEmailIsKnown() {
        $processConsentService = $this->factoryService();

        Phake::when($this->consentRepositoryMock)
            ->countTotalConsent(Phake::anyParameters())
            ->thenReturn(1);

        $configurationMock = new stdClass();
        $configurationMock->email = new stdClass();
        $configurationMock->email->sendWelcomeMail = true;

        $processConsentService->serve(null);

        Phake::verify($this->mailerMock)->sendMail(Phake::anyParameters());
    }

    public function testResponseIsSent() {
        $processConsentService = $this->factoryService();

        Phake::when($this->proxyServerMock)
            ->redirect(Phake::anyParameters())
            ->thenReturn(null);

        Phake::when($this->proxyServerMock->getBindingsModule())
            ->send(Phake::anyParameters())
            ->thenReturn(null);

        $processConsentService->serve(null);

        Phake::verify(($this->proxyServerMock->getBindingsModule()))->send(Phake::anyParameters());
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

    private function mockGlobals()
    {
        $_POST['ID'] = 'test';
        $_POST['consent'] = 'yes';
        $_SESSION['consent']['test']['response']['saml:Assertion']['saml:AttributeStatement'][0]['saml:Attribute'] = array();
        $_SESSION['consent']['test']['response']['saml:Assertion']['saml:Subject']['saml:NameID']['__v'] = 'testUser';
    }

    /**
     * @param EngineBlock_Corto_Model_Consent_Factory $this->consentFactoryMock
     * @return EngineBlock_Corto_Model_Consent
     */
    private function mockConsent()
    {
        $consentMock = Phake::mock('EngineBlock_Corto_Model_Consent');
        Phake::when($this->consentFactoryMock)
            ->create(Phake::anyParameters())
            ->thenReturn($consentMock);

        return $consentMock;
    }

    /**
     * @return EngineBlock_Corto_Filter_Command_AttributeReleasePolicy
     */
    private function mockAttributeReleasePolicyFilter()
    {
        $attributeReleasePolicyFilterMock = Phake::mock('EngineBlock_Corto_Filter_Command_AttributeReleasePolicy');
        Phake::when($attributeReleasePolicyFilterMock)->getResponseAttributes()->thenReturn(array());
        return $attributeReleasePolicyFilterMock;
    }

    private function mockConsentRepository()
    {
        $consentFactoryMock = Phake::mock('EngineBlock_Corto_Model_Consent_Repository');
        Phake::when($consentFactoryMock)
            ->isStored(Phake::anyParameters())
            ->thenReturn(false);

        return $consentFactoryMock;
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
            $this->mailerMock,
            new EngineBlock_User_PreferredNameAttributeFilter(),
            $this->attributeReleasePolicyFilterMock,
            $this->consentRepositoryMock
        );
    }
}
