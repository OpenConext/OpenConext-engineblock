<?php

use OpenConext\EngineBlockBundle\Sfo\SfoEndpoint;
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
        $basePath = realpath(__DIR__.'/../../../');

        return [
            'default' => [
                'publicFile' => $basePath . '/ci/travis/files/engineblock.crt',
                'privateFile' => $basePath . '/ci/travis/files/engineblock.key',
            ],
        ];
    }

    /**
     * @return \OpenConext\EngineBlock\Metadata\Entity\IdentityProvider
     */
    protected function getSfoEndpoint()
    {
        $basePath = realpath(__DIR__.'/../../../');
        $keyFile = $basePath . '/ci/travis/files/engineblock.crt';

        $endpoint = parent::getSfoEndpoint();

        return new SfoEndpoint($endpoint->getEntityId(), $endpoint->getSsoLocation(), $keyFile);
    }
}
