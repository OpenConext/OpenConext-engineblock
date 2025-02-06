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

use DateTimeImmutable;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationLoopGuard;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationProcedure;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationProcedureMap;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use PHPUnit\Framework\TestCase;

class AuthenticationLoopGuardTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     * @group AuthenticationState
     */
    public function authentication_loop_guard_determines_that_it_is_in_a_loop()
    {
        $maximumNumberOfAuthenticationProceduresAllowed = 1;
        $timeFrameForCheckingAuthenticationLoopInSeconds = 60000;
        $maximumAuthenticationsPerSession = 20;
        $authenticationLoopGuard = new AuthenticationLoopGuard(
            $maximumNumberOfAuthenticationProceduresAllowed,
            $timeFrameForCheckingAuthenticationLoopInSeconds,
            $maximumAuthenticationsPerSession
        );

        $serviceProvider  = new Entity(new EntityId('some.serviceprovider.example'), EntityType::SP());
        $identityProvider = new Entity(new EntityId('some.identityprovider.example'), EntityType::IdP());

        $firstProcedure  = AuthenticationProcedure::onBehalfOf($serviceProvider);
        $firstProcedure->authenticatedAt($identityProvider);
        $firstProcedure->completeOn(new DateTimeImmutable());

        $currentProcedure  = AuthenticationProcedure::onBehalfOf($serviceProvider);
        $currentProcedure->authenticatedAt($identityProvider);
        $currentProcedure->completeOn(new DateTimeImmutable());

        $pastAuthenticationProcedures = new AuthenticationProcedureMap(
            [
                '_00000000-0000-0000-0000-000000000000' => $firstProcedure,
                '_00000000-0000-0000-0000-000000000001' => $currentProcedure,
            ]
        );

        $stuckInLoop = $authenticationLoopGuard->detectsAuthenticationLoop(
            $serviceProvider,
            $pastAuthenticationProcedures
        );

        $this->assertTrue(
            $stuckInLoop,
            'The authentication loop guard should have detected an authentication loop, but it did not'
        );
    }

    /**
     * @test
     * @group AuthenticationState
     */
    public function authentication_loop_guard_determines_that_it_is_not_in_a_loop()
    {
        $maximumNumberOfAuthenticationProceduresAllowed = 2;
        $timeFrameForCheckingAuthenticationLoopInSeconds = 60000;
        $maximumAuthenticationsPerSession = 20;
        $authenticationLoopGuard = new AuthenticationLoopGuard(
            $maximumNumberOfAuthenticationProceduresAllowed,
            $timeFrameForCheckingAuthenticationLoopInSeconds,
            $maximumAuthenticationsPerSession
        );

        $serviceProvider  = new Entity(new EntityId('some.serviceprovider.example'), EntityType::SP());
        $identityProvider = new Entity(new EntityId('some.identityprovider.example'), EntityType::IdP());

        $firstProcedure  = AuthenticationProcedure::onBehalfOf($serviceProvider);
        $firstProcedure->authenticatedAt($identityProvider);
        $firstProcedure->completeOn(new DateTimeImmutable());

        $pastAuthenticationProcedures = new AuthenticationProcedureMap(
            ['_00000000-0000-0000-0000-000000000000' => $firstProcedure]
        );

        $inAuthenticationLoop = $authenticationLoopGuard->detectsAuthenticationLoop(
            $serviceProvider,
            $pastAuthenticationProcedures
        );

        $this->assertFalse(
            $inAuthenticationLoop,
            'The authentication loop guard should not have detected an authentication loop, but it did'
        );
    }
}
