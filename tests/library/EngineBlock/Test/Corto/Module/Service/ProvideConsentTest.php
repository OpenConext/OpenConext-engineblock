<?php

use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\InMemoryMetadataRepository;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationLoopGuard;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationState;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use Symfony\Component\HttpFoundation\Session\Session;

class EngineBlock_Test_Corto_Module_Service_ProvideConsentTest extends PHPUnit_Framework_TestCase
{
    /** @var EngineBlock_Corto_XmlToArray */
    private $xmlConverterMock;

    /** @var EngineBlock_Corto_Model_Consent_Factory */
    private $consentFactoryMock;

    /** @var EngineBlock_Corto_Model_Consent */
    private $consentMock;

    /** @var EngineBlock_Corto_ProxyServer */
    private $proxyServerMock;

    /** @var Session */
    private $sessionMock;

    public function setup() {
        $diContainer              = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();

        $this->proxyServerMock    = $this->mockProxyServer();
        $this->xmlConverterMock   = $this->mockXmlConverter($diContainer->getXmlConverter());
        $this->consentFactoryMock = $diContainer->getConsentFactory();
        $this->consentMock        = $this->mockConsent();
        $this->sessionMock        = $this->mockSession();
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
            ->explicitConsentWasGivenFor(Phake::anyParameters())
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
            ->explicitConsentWasGivenFor(Phake::anyParameters())
            ->thenReturn(false);
        Phake::when($this->consentFactoryMock)
            ->create(Phake::anyParameters())
            ->thenReturn($consentMock);

        return $consentMock;
    }

    private function mockSession()
    {
        $dummySp  = new Entity(new EntityId('dummy.sp.example'), EntityType::SP());
        $dummyIdp = new Entity(new EntityId('dummy.idp.example'), EntityType::IdP());

        $maximumAllowedAuthenticationProcedures = 5;
        $timeFrameForDeterminingAuthenticationLoopInSeconds = 1200;

        $authenticationLoopGuard = new AuthenticationLoopGuard(
            $maximumAllowedAuthenticationProcedures,
            $timeFrameForDeterminingAuthenticationLoopInSeconds
        );

        $authenticationState = new AuthenticationState($authenticationLoopGuard);
        $authenticationState->startAuthenticationOnBehalfOf($dummySp);
        $authenticationState->authenticateAt($dummyIdp);

        $sessionMock = Phake::mock(Session::class);
        Phake::when($sessionMock)
            ->get('authentication_state')
            ->thenReturn($authenticationState);

        return $sessionMock;
    }

    /**
     * @return EngineBlock_Corto_Module_Service_ProvideConsent
     */
    private function factoryService()
    {
        return new EngineBlock_Corto_Module_Service_ProvideConsent(
            $this->proxyServerMock,
            $this->xmlConverterMock,
            $this->consentFactoryMock,
            $this->sessionMock
        );
    }
}
