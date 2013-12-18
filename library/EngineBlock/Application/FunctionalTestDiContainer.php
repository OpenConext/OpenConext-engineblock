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
