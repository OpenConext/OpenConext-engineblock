<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\FunctionalTestingSfoGatewayMockConfiguration;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\ServiceRegistryFixture;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\EntityRegistry;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProviderFactory;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProviderFactory;
use SAML2\Constants;

class SfoContext extends AbstractSubContext
{
    /**
     * @var EntityRegistry
     */
    private $mockSpRegistry;
    /**
     * @var EntityRegistry
     */
    private $mockIdpRegistry;
    /**
     * @var FunctionalTestingSfoGatewayMockConfiguration
     */
    private $gatewayMockConfiguration;
    /**
     * @var MockIdentityProviderFactory
     */
    private $idpFactory;
    /**
     * @var MockServiceProviderFactory
     */
    private $spFactory;
    /**
     * @var ServiceRegistryFixture
     */
    private $serviceRegistryFixture;

    /**
     * @param EntityRegistry $mockSpRegistry
     * @param EntityRegistry $mockIdpRegistry
     * @param MockIdentityProviderFactory $idpFactory
     * @param MockServiceProviderFactory $spFactory
     * @param FunctionalTestingSfoGatewayMockConfiguration $gatewayMockConfiguration
     * @param ServiceRegistryFixture $serviceRegistryFixture
     */
    public function __construct(
        EntityRegistry $mockSpRegistry,
        EntityRegistry $mockIdpRegistry,
        MockIdentityProviderFactory $idpFactory,
        MockServiceProviderFactory $spFactory,
        FunctionalTestingSfoGatewayMockConfiguration $gatewayMockConfiguration,
        ServiceRegistryFixture $serviceRegistryFixture
    ) {
        $this->mockSpRegistry = $mockSpRegistry;
        $this->mockIdpRegistry = $mockIdpRegistry;
        $this->gatewayMockConfiguration = $gatewayMockConfiguration;
        $this->idpFactory = $idpFactory;
        $this->spFactory = $spFactory;
        $this->serviceRegistryFixture = $serviceRegistryFixture;
    }

    /**
     * @Given /^SFO is used$/
     */
    public function sfoIsUsed()
    {
        $basePath = realpath(__DIR__.'/../../../../../');

        // Set gateway configured IDP
        $mockEbIdp = $this->idpFactory->createNew('Sfo gateway');
        $mockEbIdp->setEntityId('https://engine.vm.openconext.org/authentication/sfo/metadata');
        $mockEbIdp->setPrivateKey($basePath.'/ci/travis/files/engineblock.key');
        $mockEbIdp->setCertificate($basePath.'/ci/travis/files/engineblock.crt');
        $this->gatewayMockConfiguration->setMockIdentityProvider($mockEbIdp);

        // Set gateway configured SP
        $mockSp = $this->spFactory->createNew('ebSfoSp');
        $mockSp->setEntityId('https://engine.vm.openconext.org/authentication/sfo/metadata');
        $this->gatewayMockConfiguration->setMockServiceProvider($mockSp);

        $this->gatewayMockConfiguration->save();
    }

    /**
     * @Given /^SFO will successfully verify a user$/
     */
    public function sfoWillsSuccessfullyVerifyAUser()
    {
        $this->gatewayMockConfiguration->unsetMessage();
        $this->gatewayMockConfiguration->save();
    }

    /**
     * @Given /^SFO will fail as the user cancelled$/
     */
    public function sfoWillFailAsTheUserCancelled()
    {
        $this->gatewayMockConfiguration->setMessage(Constants::STATUS_RESPONDER, Constants::STATUS_AUTHN_FAILED, 'Authentication cancelled by user');
        $this->gatewayMockConfiguration->save();
    }

    /**
     * @Given /^SFO will fail as the loa can not be given$/
     */
    public function sfoWillFailAsTheLoaCanNotBeGiven()
    {
        $this->gatewayMockConfiguration->setMessage(Constants::STATUS_RESPONDER, Constants::STATUS_NO_AUTHN_CONTEXT);
        $this->gatewayMockConfiguration->save();
    }

    /**
     * @Given /^I authenticate with SFO$/
     */
    public function iAuthenticateWithSfo()
    {
        $mink = $this->getMinkContext();

        $mink->pressButton('SubmitSfo');
    }


    /**
     * @Given /^the SP "([^"]*)" allows no SFO token$/
     */
    public function spAllowsNoSfo($spName)
    {
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setSpSfoAllowNoToken($mockSp->entityId())
            ->save();
    }

    /**
     * @Given /^the SP "([^"]*)" requires SFO loa "([^"]*)"$/
     */
    public function setSpSfoRequireLoa($spName, $requiredLoa)
    {
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setSpSfoRequireLoa($mockSp->entityId(), $requiredLoa)
            ->save();
    }

    /**
     * @Given /^I authenticate with SFO$/
     */
    public function iAuthenticateWithSfo()
    {
        $mink = $this->getMinkContext();

        $mink->pressButton('SubmitSfo');
    }


    /**
     * @Given /^the SP "([^"]*)" allows no SFO token$/
     */
    public function spAllowsNoSfo($spName)
    {
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setSpSfoAllowNoToken($mockSp->entityId())
            ->save();
    }

    /**
     * @Given /^the SP "([^"]*)" requires SFO loa "([^"]*)"$/
     */
    public function setSpSfoRequireLoa($spName, $requiredLoa)
    {
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setSpSfoRequireLoa($mockSp->entityId(), $requiredLoa)
            ->save();
    }
}
