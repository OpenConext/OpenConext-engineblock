<?php
/**
 * Creates mocked versions of dependencies for functional testing
 */
class EngineBlock_Application_FunctionalTestDiContainer extends EngineBlock_Application_DiContainer
{
    protected function registerServiceRegistryClient()
    {
        $this[self::SERVICE_REGISTRY_CLIENT] = function ()
        {
            return new Janus_FixtureClient();
        };
    }

    protected function registerTimeProvider()
    {
        $this[self::TIME] = function ()
        {
            return new EngineBlock_TimeProvider_Fixture();
        };
    }

    protected function registerSaml2IdGenerator()
    {
        $this[self::SAML2_ID] = function()
        {
            return new EngineBlock_Saml2_IdGenerator_Fixture();
        };
    }

    protected function registerSuperGlobalManager()
    {
        $this[self::SUPER_GLOBAL_MANAGER] = function() {
            return new EngineBlock_Application_SuperGlobalManager();
        };
    }

    public function getMessageUtilClassName()
    {
        return 'EngineBlock_Ssp_sspmod_saml_Message';
    }

    public function getUserDirectory()
    {
        return new \OpenConext\EngineBlockFunctionalTestingBundle\Mock\FakeUserDirectory();
    }

}
