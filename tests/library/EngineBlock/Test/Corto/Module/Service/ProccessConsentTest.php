<?php

use OpenConext\Component\EngineBlockMetadata\MetadataRepository\InMemoryMetadataRepository;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;

class EngineBlock_Test_Corto_Module_Service_ProccessConsentTest extends PHPUnit_Framework_TestCase
{
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
     * @var EngineBlock_Mail_Mailer
     */
    private $mailerMock;

    public function setup()
    {
        $this->proxyServerMock = $this->mockProxyServer();

        $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $this->xmlConverterMock = $this->mockXmlConverter($diContainer[EngineBlock_Application_DiContainer::XML_CONVERTER]);
        $this->consentFactoryMock = $diContainer[EngineBlock_Application_DiContainer::CONSENT_FACTORY];
        $this->mailerMock = $diContainer[EngineBlock_Application_DiContainer::MAILER];

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

    public function testConsentIsStored()
    {
        $processConsentService = $this->factoryService();

        $consentMock = $this->mockConsent();
        Phake::when($consentMock)
            ->storeConsent(Phake::anyParameters())
            ->thenReturn(true);

        $processConsentService->serve(null);

        Phake::verify(($consentMock))->storeConsent(Phake::anyParameters());
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
        /** @var EngineBlock_Corto_ProxyServer $proxyServerMock */
        $proxyServerMock = Phake::partialMock('EngineBlock_Corto_ProxyServer');
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

    private function mockGlobals()
    {
        $_POST['ID'] = 'test';
        $_POST['consent'] = 'yes';

        $assertion = new SAML2_Assertion();
        $assertion->setAttributes(array(
            'urn:mace:dir:attribute-def:mail' => 'test@test.test'
        ));

        $spRequest = new SAML2_AuthnRequest();
        $spRequest->setId('SPREQUEST');
        $spRequest->setIssuer('https://sp.example.edu');
        $spRequest = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($spRequest);

        $ebRequest = new SAML2_AuthnRequest();
        $ebRequest->setId('EBREQUEST');
        $ebRequest = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($ebRequest);

        $dummySessionLog = new Psr\Log\NullLogger();
        $authnRequestRepository = new EngineBlock_Saml2_AuthnRequestSessionRepository($dummySessionLog);
        $authnRequestRepository->store($spRequest);
        $authnRequestRepository->store($ebRequest);
        $authnRequestRepository->link($ebRequest, $spRequest);

        $sspResponse = new SAML2_Response();
        $sspResponse->setInResponseTo('EBREQUEST');
        $sspResponse->setAssertions(array($assertion));
        $_SESSION['consent']['test']['response'] = new EngineBlock_Saml2_ResponseAnnotationDecorator($sspResponse);
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
            new EngineBlock_User_PreferredNameAttributeFilter()
        );
    }
}
