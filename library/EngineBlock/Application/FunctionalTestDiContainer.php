<?php

use OpenConext\EngineBlockBundle\Stepup\StepupEndpoint;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\FakeUserDirectory;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Creates mocked versions of dependencies for functional testing
 */
class EngineBlock_Application_FunctionalTestDiContainer extends EngineBlock_Application_DiContainer
{
    public function getUserDirectory()
    {
        return new FakeUserDirectory(new Filesystem());
    }

    public function getFeatureConfiguration()
    {
        return $this->getSymfonyContainer()->get('engineblock.functional_testing.fixture.features');
    }

    public function getAuthenticationLoopGuard()
    {
        return $this->getSymfonyContainer()->get('engineblock.functional_testing.fixture.authentication_loop_guard');
    }

    public function getPdpClient()
    {
        return $this->getFunctionalTestingPdpClient();
    }

    public function getPdpClientId()
    {
        return 'Federation';
    }

    /**
     * @return \OpenConext\EngineBlockBundle\AttributeAggregation\AttributeAggregationClientInterface
     */
    public function getAttributeAggregationClient()
    {
        return $this->getSymfonyContainer()->get('engineblock.functional_testing.fixture.attribute_aggregation_client');
    }

    public function getAuthnContextClassRefBlacklistRegex()
    {
        return '/invalid-authn-context-class-ref/';
    }

    /**
     * @return array
     */
    public function getEncryptionKeysConfiguration()
    {
        $basePath = $this->container->getParameter('kernel.project_dir');

        return [
            'default' => [
                'publicFile' => $basePath . '/tests/resources/key/engineblock.crt',
                'privateFile' => $basePath . '/tests/resources/key/engineblock.pem',
            ],
        ];
    }
}
