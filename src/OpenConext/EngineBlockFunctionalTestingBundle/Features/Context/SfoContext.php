<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\FunctionalTestingSfoGatewayMockConfiguration;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\ServiceRegistryFixture;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\EntityRegistry;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProviderFactory;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProviderFactory;

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
     * @var ServiceRegistryFixture
     */
    private $serviceRegistryFixture;

    /**
     * @param EntityRegistry $mockSpRegistry
     * @param EntityRegistry $mockIdpRegistry
     * @param FunctionalTestingSfoGatewayMockConfiguration $gatewayMockConfiguration
     * @param ServiceRegistryFixture $serviceRegistryFixture
     */
    public function __construct(
        EntityRegistry $mockSpRegistry,
        EntityRegistry $mockIdpRegistry,
        FunctionalTestingSfoGatewayMockConfiguration $gatewayMockConfiguration,
        ServiceRegistryFixture $serviceRegistryFixture
    ) {
        $this->mockSpRegistry = $mockSpRegistry;
        $this->mockIdpRegistry = $mockIdpRegistry;
        $this->gatewayMockConfiguration = $gatewayMockConfiguration;
        $this->serviceRegistryFixture = $serviceRegistryFixture;
    }

    /**
     * @Given /^SFO is used$/
     */
    public function sfoIsUsed()
    {
        //todo: set feature flag?
    }

    /**
     * @Given /^SFO will successfully verify a user$/
     */
    public function sfoWillsSuccessfullyVerifyAUser()
    {
        $mink = $this->getMinkContext();

        $mink->pressButton('Submit-success');
    }

    /**
     * @Given /^I authenticate with SFO$/
     */
    public function iAuthenticateWithSfo()
    {
        $mink = $this->getMinkContext();

        $mink->pressButton('Submit-user-cancelled');
    }

    /**
     * @Given /^SFO will fail as the user cancelled$/
     */
    public function sfoWillFailAsTheUserCancelled()
    {
        $mink = $this->getMinkContext();

        $mink->pressButton('Submit-user-cancelled');
    }

    /**
     * @Given /^SFO will fail if the loa can not be given$/
     */
    public function sfoWillFailIfTheLoaCanNotBeGiven()
    {
        $mink = $this->getMinkContext();

        $mink->pressButton('Submit-unmet-loa');
    }

    /**
     * @Given /^SFO will fail on unknown invalid status$/
     */
    public function sfoWillFailOnUnknownInvalidStatus()
    {
        $mink = $this->getMinkContext();

        $mink->pressButton('Submit-unknown');
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
