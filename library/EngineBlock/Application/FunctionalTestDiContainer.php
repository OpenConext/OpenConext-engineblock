<?php

use OpenConext\EngineBlockFunctionalTestingBundle\Mock\FakeUserDirectory;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Creates mocked versions of dependencies for functional testing
 */
class EngineBlock_Application_FunctionalTestDiContainer extends EngineBlock_Application_DiContainer
{
    public function getServiceRegistryClient()
    {
        return new Janus_FixtureClient();
    }

    public function getTimeProvider()
    {
        return new EngineBlock_TimeProvider_Fixture();
    }

    public function getSaml2IdGenerator()
    {
        return new EngineBlock_Saml2_IdGenerator_Fixture();
    }

    public function getSuperGlobalManager()
    {
        return new EngineBlock_Application_SuperGlobalManager();
    }

    public function getMessageUtilClassName()
    {
        return 'EngineBlock_Ssp_sspmod_saml_Message';
    }

    public function getUserDirectory()
    {
        return new FakeUserDirectory(new Filesystem());
    }

    public function getFeatureConfiguration()
    {
        return $this->getFunctionalTestingFeatureConfiguration();
    }

    public function getAuthenticationLoopGuard()
    {
        return $this->getFunctionalTestingAuthenticationLoopGuard();
    }
}
