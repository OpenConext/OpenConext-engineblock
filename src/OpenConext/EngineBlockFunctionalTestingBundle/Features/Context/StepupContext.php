<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\FunctionalTestingStepupGatewayMockConfiguration;
use OpenConext\EngineBlockFunctionalTestingBundle\Fixtures\ServiceRegistryFixture;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\EntityRegistry;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockIdentityProvider;
use OpenConext\EngineBlockFunctionalTestingBundle\Mock\MockServiceProvider;
use RuntimeException;

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
        $page = $this->getMinkContext()->getSession()->getPage();
        $form = $page->find('css', 'form[action*="/authentication/stepup/consume-assertion"]');

        if ($form === null) {
            throw new RuntimeException('No form found for "/authentication/stepup/consume-assertion"');
        }

        $button = $form->find('css', 'input[type="submit"][value="Submit-success"]');
        if ($button === null) {
            throw new RuntimeException('No form with submit button found for "/authentication/stepup/consume-assertion"');
        }

        $button->click();
    }

    /**
     * @Given /^Stepup will successfully verify a user with override entityID$/
     */
    public function stepupWillsSuccessfullyVerifyAUserAndUpdateAudience()
    {
        $mink = $this->getMinkContext();

        $mink->pressButton('Submit-success-audience');
    }

    /**
     * @Given /^Stepup will successfully verify a user with a LoA 2 token$/
     */
    public function stepupWillsSuccessfullyVerifyAUserLoa2()
    {
        $mink = $this->getMinkContext();

        $mink->pressButton('Submit-loa2');
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
     * @Given /^the SP "([^"]*)" forces stepup authentication$/
     */
    public function spForcesAuthn($spName)
    {
        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setStepupForceAuthn($mockSp->entityId(), true)
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
