<?php
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
        $proxyServer = new EngineBlock_Corto_ProxyServer();

        $remoteEntities = $this->factoryRemoteEntities();
        $proxyServer->setRemoteEntities($remoteEntities);

        $originalRequest = $this->factoryOriginalRequest();
        $idpEntityId = 'testIdp';
        $scope = array();
        $enhancedRequest = $proxyServer->createEnhancedRequest($originalRequest, $idpEntityId, $scope);

        $this->assertNotContains('_Format', $enhancedRequest['samlp:NameIDPolicy']);
    }

    public function testNameIDFormatIsSetFromRemoteMetaData()
    {
        $proxyServer = new EngineBlock_Corto_ProxyServer();

        $remoteEntities = $this->factoryRemoteEntities();
        $remoteEntities['testIdp']['NameIDFormat'] = 'fooFormat';
        $proxyServer->setRemoteEntities($remoteEntities);

        $originalRequest = $this->factoryOriginalRequest();
        $idpEntityId = 'testIdp';
        $scope = array();
        $enhancedRequest = $proxyServer->createEnhancedRequest($originalRequest, $idpEntityId, $scope);

        $this->assertEquals($enhancedRequest['samlp:NameIDPolicy']['_Format'], 'fooFormat');
    }

    public function testGettingCurrentEntityIsProxiedViaGetRemoteEntity()
    {
        $proxyServer = new EngineBlock_Corto_ProxyServer();
        $currentEntity = array('EntityID' => 'testEntity');
        $proxyServer->setCurrentEntities(array($currentEntity));

        $this->assertEquals($currentEntity, $proxyServer->getRemoteEntity('testEntity'));
    }

    /**
     * @return array
     */
    private function factoryOriginalRequest()
    {
        $originalRequest = array();
        $originalRequest['_ForceAuthn'] = null;
        $originalRequest['_IsPassive'] = null;
        $originalRequest['saml:Issuer']['__v'] = null;

        return $originalRequest;
    }

    /**
     * @return array
     */
    private function factoryRemoteEntities()
    {
        $remoteEntities = array(
            'testIdp' => array(
                'SingleSignOnService' => array(
                    'Binding' => null,
                    'Location' => null
                )
            )
        );

        return $remoteEntities;
    }
}