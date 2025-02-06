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

namespace OpenConext\EngineBlockBundle\Tests;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationLoopGuard;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationState;
use OpenConext\EngineBlockBundle\Exception\LogicException;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use PHPUnit\Framework\TestCase;

class AuthenticationStateTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     * @group Authentication
     */
    public function an_authentication_procedure_cannot_be_authenticated_if_it_has_not_been_started()
    {
        $authenticationLoopGuard = new AuthenticationLoopGuard(5, 30, 20);

        $identityProvider = new Entity(new EntityId('https://my-identity-provider.example'), EntityType::IdP());

        $authenticationState = new AuthenticationState($authenticationLoopGuard);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The requested authentication procedure with requestId "_00000000-0000-0000-0000-000000000000" couldn\'t be found in the session storage.');

        $authenticationState->authenticatedAt('_00000000-0000-0000-0000-000000000000', $identityProvider);
    }

    /**
     * @test
     * @group Authentication
     */
    public function an_authentication_procedure_cannot_be_completed_if_it_has_not_been_started()
    {
        $authenticationLoopGuard = new AuthenticationLoopGuard(5, 30, 20);

        $authenticationState = new AuthenticationState($authenticationLoopGuard);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The requested authentication procedure with requestId "_00000000-0000-0000-0000-000000000000" couldn\'t be found in the session storage in order to complete.');

        $authenticationState->completeCurrentProcedure('_00000000-0000-0000-0000-000000000000');
    }

    /**
     * @test
     * @group Authentication
     */
    public function an_authentication_procedure_cannot_be_completed_if_it_has_not_been_authenticated()
    {
        $authenticationLoopGuard = new AuthenticationLoopGuard(5, 30, 20);

        $serviceProvider = new Entity(new EntityId('https://my-service-provider.example'), EntityType::SP());

        $requestId = '_00000000-0000-0000-0000-000000000000';

        $authenticationState = new AuthenticationState($authenticationLoopGuard);
        $authenticationState->startAuthenticationOnBehalfOf($requestId, $serviceProvider);

        $this->expectExceptionMessage('The requested authentication procedure with requestId "_00000000-0000-0000-0000-000000000000" has not been authenticated.');
        $authenticationState->completeCurrentProcedure($requestId);
    }

    /**
     * @test
     * @group Authentication
     */
    public function an_authentication_procedure_can_be_completed_multiple_times()
    {
        $authenticationLoopGuard = new AuthenticationLoopGuard(5, 30, 20);

        $serviceProvider = new Entity(new EntityId('https://my-service-provider.example'), EntityType::SP());
        $identityProvider = new Entity(new EntityId('https://my-service-provider.example'), EntityType::IdP());

        $requestId = '_00000000-0000-0000-0000-000000000000';

        $authenticationState = new AuthenticationState($authenticationLoopGuard);
        $authenticationState->startAuthenticationOnBehalfOf($requestId, $serviceProvider);
        $authenticationState->authenticatedAt($requestId, $identityProvider);
        $authenticationState->completeCurrentProcedure($requestId);
        $authenticationState->completeCurrentProcedure($requestId);

        self::assertTrue($authenticationState->isAuthenticated());
    }

    /**
     * @test
     * @group Authentication
     */
    public function an_authentication_procedure_is_not_authenticated_before_consent()
    {
        $authenticationLoopGuard = new AuthenticationLoopGuard(5, 30, 20);

        $serviceProvider = new Entity(new EntityId('https://my-service-provider.example'), EntityType::SP());
        $identityProvider = new Entity(new EntityId('https://my-service-provider.example'), EntityType::IdP());

        $requestId = '_00000000-0000-0000-0000-000000000000';

        $authenticationState = new AuthenticationState($authenticationLoopGuard);
        $authenticationState->startAuthenticationOnBehalfOf($requestId, $serviceProvider);
        $authenticationState->authenticatedAt($requestId, $identityProvider);

        self::assertFalse($authenticationState->isAuthenticated());

        $authenticationState->completeCurrentProcedure($requestId);

        self::assertTrue($authenticationState->isAuthenticated());
    }
}
