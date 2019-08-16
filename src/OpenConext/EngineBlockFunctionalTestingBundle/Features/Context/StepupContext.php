<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\FunctionalTestingStepupGatewayMockConfiguration;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\ServiceRegistryFixture;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\EntityRegistry;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProvider;

class StepupContext extends AbstractSubContext
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
     * @var FunctionalTestingStepupGatewayMockConfiguration
     */
    private $gatewayMockConfiguration;
    /**
     * @var ServiceRegistryFixture
     */
    private $serviceRegistryFixture;

    /**
     * @param EntityRegistry $mockSpRegistry
     * @param EntityRegistry $mockIdpRegistry
     * @param FunctionalTestingStepupGatewayMockConfiguration $gatewayMockConfiguration
     * @param ServiceRegistryFixture $serviceRegistryFixture
     */
    public function __construct(
        EntityRegistry $mockSpRegistry,
        EntityRegistry $mockIdpRegistry,
        FunctionalTestingStepupGatewayMockConfiguration $gatewayMockConfiguration,
        ServiceRegistryFixture $serviceRegistryFixture
    ) {
        $this->mockSpRegistry = $mockSpRegistry;
        $this->mockIdpRegistry = $mockIdpRegistry;
        $this->gatewayMockConfiguration = $gatewayMockConfiguration;
        $this->serviceRegistryFixture = $serviceRegistryFixture;
    }

    /**
     * @Given /^Stepup will successfully verify a user$/
     */
    public function stepupWillsSuccessfullyVerifyAUser()
    {
        $mink = $this->getMinkContext();

        $mink->pressButton('Submit-success');
    }

    /**
     * @Given /^I authenticate with Stepup$/
     */
    public function iAuthenticateWithStepup()
    {
        $mink = $this->getMinkContext();

        $mink->pressButton('Submit-user-cancelled');
    }

    /**
     * @Given /^Stepup will fail as the user cancelled$/
     */
    public function stepupWillFailAsTheUserCancelled()
    {
        $mink = $this->getMinkContext();

        $mink->pressButton('Submit-user-cancelled');
    }

    /**
     * @Given /^Stepup will fail if the LoA can not be given$/
     */
    public function stepupWillFailIfTheLoaCanNotBeGiven()
    {
        $mink = $this->getMinkContext();

        $mink->pressButton('Submit-unmet-loa');
    }

    /**
     * @Given /^Stepup will fail on unknown invalid status$/
     */
    public function stepupWillFailOnUnknownInvalidStatus()
    {
        $mink = $this->getMinkContext();

        $mink->pressButton('Submit-unknown');
    }


    /**
     * @Given /^the SP "([^"]*)" allows no Stepup token$/
     */
    public function spAllowsNoStepup($spName)
    {
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setSpStepupAllowNoToken($mockSp->entityId())
            ->save();
    }

    /**
     * @Given /^the SP "([^"]*)" requires Stepup LoA "([^"]*)"$/
     */
    public function setSpStepupRequireLoa($spName, $requiredLoa)
    {
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setSpStepupRequireLoa($mockSp->entityId(), $requiredLoa)
            ->save();
    }

    /**
     * @Given /^the IdP "([^"]*)" requires Stepup LoA "([^"]*)" for SP "([^"]*)"$/
     */
    public function setIdpStepupRequireLoaFor($idpName, $requiredLoa, $spName)
    {
        /** @var MockIdentityProvider $mockIdp */
        $mockIdp = $this->mockIdpRegistry->get($idpName);

        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setIdpStepupConnections($mockIdp->entityId(), [$mockSp->entityId() => $requiredLoa])
            ->save();
    }
}