<?php
/**
 * Creates mocked versions of dependencies for functional testing
 */
class EngineBlock_Application_FunctionalTestDiContainer extends EngineBlock_Application_DiContainer
{
    protected function registerServiceRegistryClient()
    {
        $this[self::SERVICE_REGISTRY_CLIENT] = $this->share(function (EngineBlock_Application_DiContainer $container) {
            $serviceRegistryClient = Phake::mock('Janus_Client_CacheProxy');

            $resourcesDir = realpath(ENGINEBLOCK_FOLDER_ROOT . 'tests/resources/serviceregistry');
            Phake::when($serviceRegistryClient)->getIdpList()->thenReturn(require_once $resourcesDir . '/idpList.php');
            Phake::when($serviceRegistryClient)->getSPList()->thenReturn(require_once $resourcesDir . '/spList.php');
            $allowedIdpsConfig = require_once $resourcesDir . '/allowedIdps.php';
            foreach($allowedIdpsConfig as $spEntityId => $idps) {
                Phake::when($serviceRegistryClient)->getAllowedIdps($spEntityId)->thenReturn($idps);
            }

            Phake::when($serviceRegistryClient)->isConnectionAllowed(Phake::anyParameters())->thenReturn(array(true));
            Phake::when($serviceRegistryClient)->getArp(Phake::anyParameters())->thenReturn(array());

            return $serviceRegistryClient;
        });
    }

    protected function registerSaml2TimestampProvider()
    {
        $this[self::SAML2_TIMESTAMP] = $this->share(function ()
        {
            return new EngineBlock_Saml2_TimestampProvider_Fixture();
        });
    }

    protected function registerSaml2IdGenerator()
    {
        $this[self::SAML2_ID] = $this->share(function()
            {
                return new EngineBlock_Saml2_IdGenerator_Fixture();
            }
        );
    }
}
