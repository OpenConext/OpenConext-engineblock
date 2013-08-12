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

            return $serviceRegistryClient;
        });
    }
}