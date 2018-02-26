<?php
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\InMemoryMetadataRepository;
use SAML2\AuthnRequest;

/**
 * Note: this Test only tests setting of NameIDFormat, add other tests if required
 */
class EngineBlock_Test_Corto_ProxyServerTest extends PHPUnit_Framework_TestCase
{
    public function testNameIDFormatIsNotSetByDefault()
    {
        $proxyServer = $this->factoryProxyServer();

        $originalRequest = $this->factoryOriginalRequest();
        $identityProvider = $proxyServer->getRepository()->fetchIdentityProviderByEntityId('testIdp');
        /** @var AuthnRequest $enhancedRequest */
        $enhancedRequest = EngineBlock_Saml2_AuthnRequestFactory::createFromRequest(
            $originalRequest,
            $identityProvider,
            $proxyServer
        );

        $nameIdPolicy = $enhancedRequest->getNameIdPolicy();

        $this->assertNotContains(
            'Format',
            array_keys($nameIdPolicy),
            'The NameIDPolicy should not contain the key "Format"',
            false,
            true,
            true
        );
    }

    public function testAllowCreateIsSet()
    {
        $proxyServer = $this->factoryProxyServer();

        $originalRequest = $this->factoryOriginalRequest();
        $identityProvider = $proxyServer->getRepository()->fetchIdentityProviderByEntityId('testIdp');
        /** @var AuthnRequest $enhancedRequest */
        $enhancedRequest = EngineBlock_Saml2_AuthnRequestFactory::createFromRequest(
            $originalRequest,
            $identityProvider,
            $proxyServer
        );

        $nameIdPolicy = $enhancedRequest->getNameIdPolicy();

        $this->assertContains(
            'AllowCreate',
            array_keys($nameIdPolicy),
            'The NameIDPolicy should contain the key "AllowCreate"',
            false,
            true,
            true
        );
    }

    public function testNameIDFormatIsSetFromRemoteMetaData()
    {
        $proxyServer = $this->factoryProxyServer();
        $originalRequest = $this->factoryOriginalRequest();

        $identityProvider = $proxyServer->getRepository()->fetchIdentityProviderByEntityId('testIdp');
        $identityProvider->nameIdFormat = 'fooFormat';

        /** @var AuthnRequest $enhancedRequest */
        $enhancedRequest = EngineBlock_Saml2_AuthnRequestFactory::createFromRequest(
            $originalRequest,
            $identityProvider,
            $proxyServer
        );

        $nameIdPolicy = $enhancedRequest->getNameIdPolicy();
        $this->assertEquals($nameIdPolicy['Format'], 'fooFormat');
    }

    /**
     * @return array
     */
    private function factoryOriginalRequest()
    {
        $originalRequest = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator(new AuthnRequest());

        return $originalRequest;
    }


    private function factoryProxyServer()
    {
        $twig = Mockery::mock(Twig_Environment::class);
        $proxyServer = new EngineBlock_Corto_ProxyServer($twig);
        $proxyServer->setHostName('test-host');

        $proxyServer->setRepository(new InMemoryMetadataRepository(
            array(new IdentityProvider('testIdp')),
            array()
        ));

        return $proxyServer;
    }
}
