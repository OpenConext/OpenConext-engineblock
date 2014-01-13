<?php
/**
 * Creates mocked versions of dependencies for functional testing
 */
class EngineBlock_Application_FunctionalTestDiContainer extends EngineBlock_Application_DiContainer
{
    protected function registerServiceRegistryClient()
    {
        $this[self::SERVICE_REGISTRY_CLIENT] = $this->share(function ()
        {
            return new Janus_FixtureClient();
        });
    }

    protected function registerTimeProvider()
    {
        $this[self::TIME] = $this->share(function ()
        {
            return new EngineBlock_TimeProvider_Fixture();
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

    public function getMessageUtilClassName()
    {
        return 'EngineBlock_Ssp_sspmod_saml_Message';
    }
}
