<?php

/**
 * Copyright 2016 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Tests;

use OpenConext\EngineBlockBundle\Authentication\AuthenticationState;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use PHPUnit_Framework_TestCase as TestCase;

class AuthenticationStateTest extends TestCase
{
    /**
     * @test
     * @group AuthenticationState
     */
    public function an_authentication_state_determines_that_it_is_in_an_authentication_loop()
    {
        $someServiceProvider  = new Entity(new EntityId('some.serviceprovider.example'), EntityType::SP());
        $someIdentityProvider = new Entity(new EntityId('some.serviceprovider.example'), EntityType::IdP());

        $maximumAuthenticationCyclesAllowed = 3;
        $authenticationCycleTimeFrame = 5;

        $authenticationState = new AuthenticationState;
        $authenticationState->startAuthenticationOnBehalfOf($someServiceProvider);
        $authenticationState->authenticateAt($someIdentityProvider);
        $authenticationState->completeCurrent();

        $authenticationState->startAuthenticationOnBehalfOf($someServiceProvider);
        $authenticationState->authenticateAt($someIdentityProvider);
        $authenticationState->completeCurrent();

        $authenticationState->startAuthenticationOnBehalfOf($someServiceProvider);
        $authenticationState->authenticateAt($someIdentityProvider);
        $authenticationState->completeCurrent();

        $authenticationState->startAuthenticationOnBehalfOf($someServiceProvider);
        $authenticationState->authenticateAt($someIdentityProvider);
        $authenticationState->completeCurrent();

        $isInLoop = $authenticationState->isInLoop(
            $someServiceProvider,
            $maximumAuthenticationCyclesAllowed,
            $authenticationCycleTimeFrame
        );

        $this->assertTrue($isInLoop, 'The authentication state should determine that it is in a loop');
    }
    /**
     * @test
     * @group AuthenticationState
     */
    public function an_authentication_state_determines_that_it_is_not_in_an_authentication_loop()
    {
        $someServiceProvider  = new Entity(new EntityId('some.serviceprovider.example'), EntityType::SP());
        $someIdentityProvider = new Entity(new EntityId('some.serviceprovider.example'), EntityType::IdP());

        $maximumAuthenticationCyclesAllowed = 3;
        $authenticationCycleTimeFrame = 5;

        $authenticationState = new AuthenticationState;
        $authenticationState->startAuthenticationOnBehalfOf($someServiceProvider);
        $authenticationState->authenticateAt($someIdentityProvider);
        $authenticationState->completeCurrent();

        $isInLoop = $authenticationState->isInLoop(
            $someServiceProvider,
            $maximumAuthenticationCyclesAllowed,
            $authenticationCycleTimeFrame
        );

        $this->assertFalse($isInLoop, 'The authentication state should determine that it is not in a loop');
    }
}
