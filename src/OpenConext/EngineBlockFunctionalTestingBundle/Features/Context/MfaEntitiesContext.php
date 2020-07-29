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

class MfaEntitiesContext extends AbstractSubContext
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
     * @var ServiceRegistryFixture
     */
    private $serviceRegistryFixture;

    /**
     * @param EntityRegistry $mockSpRegistry
     * @param EntityRegistry $mockIdpRegistry
     * @param ServiceRegistryFixture $serviceRegistryFixture
     */
    public function __construct(
        EntityRegistry $mockSpRegistry,
        EntityRegistry $mockIdpRegistry,
        ServiceRegistryFixture $serviceRegistryFixture
    ) {
        $this->mockSpRegistry = $mockSpRegistry;
        $this->mockIdpRegistry = $mockIdpRegistry;
        $this->serviceRegistryFixture = $serviceRegistryFixture;
    }

    /**
     * @Given /^the IdP "([^"]*)" is configured for MFA authn method "([^"]*)" for SP "([^"]*)"$/
     */
    public function setIdpConfiguredMfaAuthnMethodFor($idpName, $authncontextclassref, $spName)
    {
        /** @var MockIdentityProvider $mockIdp */
        $mockIdp = $this->mockIdpRegistry->get($idpName);

        /** @var MockServiceProvider $mockSp */
        $mockSp = $this->mockSpRegistry->get($spName);

        $this->serviceRegistryFixture
            ->setMfaEntities($mockIdp->entityId(), [[
                'name' => $mockSp->entityId(),
                'level' => $authncontextclassref,
            ]])
            ->save();
    }
}
