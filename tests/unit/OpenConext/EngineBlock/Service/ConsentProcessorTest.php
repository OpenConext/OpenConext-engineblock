<?php

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\InMemoryMetadataRepository;
use OpenConext\EngineBlock\Service\AuthenticationStateHelperInterface;
use OpenConext\EngineBlock\Service\AuthenticationStateHelperInterfacee;
use OpenConext\EngineBlock\Service\ConsentProcessor\ConsentProcessor;
use OpenConext\EngineBlock\Service\ConsentProcessor\ConsentProcessorProxyServerInterface;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationStateInterface;
use SAML2\Assertion;
use SAML2\AuthnRequest;
use SAML2\Response;
use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Mockery as m;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @mark
 */
class ConsentProcessorTest extends TestCase
{
    /**
     * @var ConsentProcessorProxyServerInterface
     */
    private $proxyServerMock;

    /**
     * @var ConsentFactoryInterface
     */
    private $consentFactoryMock;

    /**
     * @var AuthenticationStateHelperInterfacee
     */
    private $authnStateHelperMock;

    /**
     * @var RequestStack
     */
    private $requestStackMock;

    /**
     * @var m\Mock|Request
     */
    private $requestMock;

    /**
     * @var m\Mock|Session
     */
    private $sessionMock;

    public function setup()
    {
        $this->markTestSkipped("Migration to Symfony tests space requires changes that are for now out of scope.");
        $diContainer = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();

        $this->proxyServerMock = $this->mockProxyServer();
        $this->consentFactoryMock = $diContainer->getConsentFactory();
        $this->authnStateHelperMock = $this->mockAuthnStateHelper();
        $this->sessionMock = m::mock(Session::class);
        $this->requestMock = m::mock(Request::class);
        $this->requestStackMock = $this->mockRequestStack();
        $this->mockGlobals();
    }

    public function testSessionLostExceptionIfNoSession()
    {
        $this->expectException(EngineBlock_Corto_Module_Services_SessionLostException::class);
        $this->expectExceptionMessage('Session lost after consent');

        $processConsentService = $this->factoryService();

        unset($_SESSION['consent']);

        $processConsentService->serve();
    }

    public function testSessionLostExceptionIfPostIdNotInSession()
    {
        $this->expectException(EngineBlock_Corto_Module_Services_SessionLostException::class);
        $this->expectExceptionMessage('Stored response for ResponseID "test" not found');

        unset($_SESSION['consent']['test']);

        $processConsentService = $this->factoryService();
        $processConsentService->serve(null);
    }

    public function testRedirectToFeedbackPageIfConsentNotInPost()
    {
        $this->expectException(EngineBlock_Corto_Exception_NoConsentProvided::class);

        $processConsentService = $this->factoryService();

        unset($_POST['consent']);
        $processConsentService->serve(null);
    }

    public function testConsentIsStored()
    {
        $processConsentService = $this->factoryService();

        $consentMock = $this->mockConsent();
        Phake::when($consentMock)
            ->giveExplicitConsentFor(Phake::anyParameters())
            ->thenReturn(true);

        $processConsentService->serve(null);

        Phake::verify(($consentMock))->giveExplicitConsentFor(Phake::anyParameters());
    }

    public function testResponseIsSent()
    {
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
        // Mock twig, a dependency of proxy server
        $twigMock = Phake::mock(Twig_Environment::class);

        // Mock proxy server
        $_SERVER['HTTP_HOST'] = 'test-host';
        /** @var EngineBlock_Corto_ProxyServer $proxyServerMock */
        $proxyServerMock = Phake::partialMock('EngineBlock_Corto_ProxyServer', $twigMock);
        $proxyServerMock
            ->setRepository(
                new InMemoryMetadataRepository(
                    array(),
                    array(new ServiceProvider('https://sp.example.edu'))
                )
            )
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

    private function mockGlobals()
    {
        $this->requestMock
            ->shouldReceive('request->get')
            ->with('ID')
            ->andReturn('test');

        $this->requestMock
            ->shouldReceive('request->has')
            ->with('consent')
            ->andRetur(true);

        $this->requestMock
            ->shouldReceive('request->get')
            ->with('consent')
            ->andReturn('yes');

        $this->requestMock
            ->shouldReceive('getSession')
            ->andReturn($this->sessionMock);

        $assertion = new Assertion();
        $assertion->setAttributes(
            array(
                'urn:mace:dir:attribute-def:mail' => 'test@test.test',
            )
        );

        $spRequest = new AuthnRequest();
        $spRequest->setId('SPREQUEST');
        $spRequest->setIssuer('https://sp.example.edu');
        $spRequest = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($spRequest);

        $ebRequest = new AuthnRequest();
        $ebRequest->setId('EBREQUEST');
        $ebRequest = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($ebRequest);

        $dummySessionLog = new Psr\Log\NullLogger();
        $authnRequestRepository = new EngineBlock_Saml2_AuthnRequestSessionRepository($dummySessionLog);
        $authnRequestRepository->store($spRequest);
        $authnRequestRepository->store($ebRequest);
        $authnRequestRepository->link($ebRequest, $spRequest);

        $sspResponse = new Response();
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
            ->explicitConsentWasGivenFor(Phake::anyParameters())
            ->thenReturn(false);
        Phake::when($this->consentFactoryMock)
            ->create(Phake::anyParameters())
            ->thenReturn($consentMock);

        return $consentMock;
    }

    private function mockAuthnStateHelper()
    {
        $authStateMock = Phake::mock(AuthenticationStateInterface::class);

        $helperMock = Phake::mock(AuthenticationStateHelperInterface::class);
        Phake::when($helperMock)
            ->getAuthenticationState()
            ->thenReturn($authStateMock);

        return $helperMock;
    }

    private function mockRequestStack()
    {
        $requestStackMock = Mockery::mock(RequestStack::class);

        $requestStackMock->shouldReceive('getCurrentRequest')
            ->andReturn($this->requestMock);

        return $requestStackMock;
    }

    private function factoryService()
    {
        $processor = new ConsentProcessor(
            $this->consentFactoryMock,
            $this->authnStateHelperMock,
            $this->requestStackMock
        );

        $processor->setProxyServer($this->proxyServerMock);

        return $processor;
    }
}
