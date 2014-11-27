<?php
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProviderEntity;
use OpenConext\Component\EngineBlockMetadata\Entity\MetadataRepository\InMemoryMetadataRepository;

/**
 * Note: this Test only tests setting of NameIDFormat, add other tests if required
 */
class EngineBlock_Test_Corto_ProxyServerTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        // Mock Global server object which for obvious reasons does not exist in a CLI environment
        global $_SERVER;
        $_SERVER['HTTP_HOST'] = null;
    }

    public function testNameIDFormatIsNotSetByDefault()
    {
        $proxyServer = $this->factoryProxyServer();

        $originalRequest = $this->factoryOriginalRequest();
        $identityProvider = $proxyServer->getRepository()->fetchIdentityProviderByEntityId('testIdp');
        /** @var SAML2_AuthnRequest $enhancedRequest */
        $enhancedRequest = EngineBlock_Saml2_AuthnRequestFactory::createFromRequest(
            $originalRequest,
            $identityProvider,
            $proxyServer
        );

        $this->assertNotContains('Format', $enhancedRequest->getNameIdPolicy());
    }

    public function testNameIDFormatIsSetFromRemoteMetaData()
    {
        $proxyServer = $this->factoryProxyServer();
        $originalRequest = $this->factoryOriginalRequest();

        $identityProvider = $proxyServer->getRepository()->fetchIdentityProviderByEntityId('testIdp');
        $identityProvider->nameIdFormat = 'fooFormat';

        /** @var SAML2_AuthnRequest $enhancedRequest */
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
        $originalRequest = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator(new SAML2_AuthnRequest());

        return $originalRequest;
    }


    private function factoryProxyServer()
    {
        $proxyServer = new EngineBlock_Corto_ProxyServer();

        $proxyServer->setRepository(new InMemoryMetadataRepository(
            array(new IdentityProviderEntity('testIdp')),
            array()
        ));

        return $proxyServer;
    }
}
