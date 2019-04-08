<?php

/**
 * Copyright 2017 SURFnet B.V.
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

use OpenConext\EngineBlockBundle\Authentication\AuthenticationLoopGuard;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationState;
use OpenConext\EngineBlockBundle\Exception\LogicException;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use PHPUnit_Framework_TestCase as TestCase;

class AuthenticationStateTest extends TestCase
{
    /**
     * @test
     * @group Authentication
     */
    public function an_authentication_procedure_cannot_be_authenticated_if_it_has_not_been_started()
    {
        $authenticationLoopGuard = new AuthenticationLoopGuard(5, 30);

        $identityProvider = new Entity(new EntityId('https://my-identity-provider.example'), EntityType::IdP());

        $authenticationState = new AuthenticationState($authenticationLoopGuard);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('authentication procedure has not been started');

        $authenticationState->authenticatedAt('_00000000-0000-0000-0000-000000000000', $identityProvider);
    }

    /**
     * @test
     * @group Authentication
     */
    public function an_authentication_procedure_cannot_be_completed_if_it_has_not_been_started()
    {
        $authenticationLoopGuard = new AuthenticationLoopGuard(5, 30);

        $authenticationState = new AuthenticationState($authenticationLoopGuard);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('authentication procedure has not been started');

        $authenticationState->completeCurrentProcedure('_00000000-0000-0000-0000-000000000000');
    }

    /**
     * @test
     * @group Authentication
     */
    public function an_authentication_procedure_cannot_be_completed_if_it_has_not_been_authenticated()
    {
        $authenticationLoopGuard = new AuthenticationLoopGuard(5, 30);

        $serviceProvider = new Entity(new EntityId('https://my-service-provider.example'), EntityType::SP());

        $requestId = '_00000000-0000-0000-0000-000000000000';

        $authenticationState = new AuthenticationState($authenticationLoopGuard);
        $authenticationState->startAuthenticationOnBehalfOf($requestId, $serviceProvider);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('authentication procedure has not been authenticated');

        $authenticationState->completeCurrentProcedure($requestId);
    }
}
