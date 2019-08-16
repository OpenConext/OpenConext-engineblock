<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Fixtures;

use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProviderFactory;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProviderFactory;

final class FunctionalTestingStepupGatewayMockConfiguration
{
    /**
     * @var MockIdentityProviderFactory
     */
    private $mockIdentityProviderFactory;

    /**
     * @var MockServiceProviderFactory
     */
    private $mockServiceProviderFactory;

    /**
     * @var MockIdentityProvider
     */
    private $mockIdentityProvider;

    /**
     * @var MockServiceProvider
     */
    private $mockServiceProvider;

    public function __construct(
        MockIdentityProviderFactory $mockIdentityProviderFactory,
        MockServiceProviderFactory $mockServiceProviderFactory
    ) {
        $this->mockIdentityProviderFactory = $mockIdentityProviderFactory;
        $this->mockServiceProviderFactory = $mockServiceProviderFactory;

        $basePath = realpath(__DIR__.'/../../../../');

        // Set gateway configured IDP
        $mockEbIdp = $this->mockIdentityProviderFactory->createNew('Stepup gateway');
        $mockEbIdp->setEntityId('https://engine.vm.openconext.org/authentication/stepup/metadata');
        $mockEbIdp->setPrivateKey($basePath . '/tests/resources/key/engineblock.pem');
        $mockEbIdp->setCertificate($basePath . '/tests/resources/key/engineblock.crt');

        $this->mockIdentityProvider = $mockEbIdp;

        // Set gateway configured SP
        $mockSp = $this->mockServiceProviderFactory->createNew('ebStepupSp');
        $mockSp->setEntityId('https://engine.vm.openconext.org/authentication/stepup/metadata');

        $this->mockServiceProvider = $mockSp;
    }

    /**
     * @return string
     */
    public function getIdentityProviderEntityId()
    {
        return $this->mockIdentityProvider->entityId();
    }

    /**
     * @return string
     */
    public function getIdentityProviderPublicKeyCertData()
    {
        return $this->addPublicKeyEnvelope($this->mockIdentityProvider->publicKeyCertData());
    }

    /**
     * @return string
     */
    public function getIdentityProviderGetPrivateKeyPem()
    {
        return $this->mockIdentityProvider->getPrivateKeyPem();
    }

    /**
     * @return string
     */
    public function getServiceProviderEntityId()
    {
        return $this->mockServiceProvider->entityId();
    }

    private function addPublicKeyEnvelope($key)
    {
        return "-----BEGIN CERTIFICATE-----\n" . wordwrap($key, 64, "\n", true) . "\n-----END CERTIFICATE-----";
    }
}
