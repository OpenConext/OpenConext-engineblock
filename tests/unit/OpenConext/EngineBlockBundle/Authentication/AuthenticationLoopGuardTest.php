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

use DateTimeImmutable;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationLoopGuard;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationProcedure;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationProcedureList;
use OpenConext\EngineBlockBundle\Exception\StuckInAuthenticationLoopException;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use PHPUnit_Framework_TestCase as TestCase;

class AuthenticationLoopGuardTest extends TestCase
{
    /**
     * @test
     * @group AuthenticationState
     */
    public function authentication_loop_guard_determines_that_it_is_in_a_loop()
    {
        $this->expectException(StuckInAuthenticationLoopException::class);
        $this->expectExceptionMessage('stuck in an authentication loop');

        $maximumNumberOfAuthenticationProceduresAllowed = 1;
        $timeFrameForCheckingAuthenticationLoopInSeconds = 60000;
        $authenticationLoopGuard = new AuthenticationLoopGuard(
            $maximumNumberOfAuthenticationProceduresAllowed,
            $timeFrameForCheckingAuthenticationLoopInSeconds
        );

        $serviceProvider  = new Entity(new EntityId('some.serviceprovider.example'), EntityType::SP());
        $identityProvider = new Entity(new EntityId('some.identityprovider.example'), EntityType::IdP());

        $firstProcedure  = AuthenticationProcedure::onBehalfOf($serviceProvider);
        $firstProcedure->authenticateAt($identityProvider);
        $firstProcedure->completeOn(new DateTimeImmutable());

        $currentProcedure  = AuthenticationProcedure::onBehalfOf($serviceProvider);
        $currentProcedure->authenticateAt($identityProvider);
        $currentProcedure->completeOn(new DateTimeImmutable());

        $pastAuthenticationProcedures = new AuthenticationProcedureList([$firstProcedure, $currentProcedure]);

        $authenticationLoopGuard->assertNotStuckInLoop($serviceProvider, $pastAuthenticationProcedures);
    }

    /**
     * @test
     * @group AuthenticationState
     */
    public function authentication_loop_guard_determines_that_it_is_not_in_a_loop()
    {
        $maximumNumberOfAuthenticationProceduresAllowed = 2;
        $timeFrameForCheckingAuthenticationLoopInSeconds = 60000;
        $authenticationLoopGuard = new AuthenticationLoopGuard(
            $maximumNumberOfAuthenticationProceduresAllowed,
            $timeFrameForCheckingAuthenticationLoopInSeconds
        );

        $serviceProvider  = new Entity(new EntityId('some.serviceprovider.example'), EntityType::SP());
        $identityProvider = new Entity(new EntityId('some.identityprovider.example'), EntityType::IdP());

        $firstProcedure  = AuthenticationProcedure::onBehalfOf($serviceProvider);
        $firstProcedure->authenticateAt($identityProvider);
        $firstProcedure->completeOn(new DateTimeImmutable());

        $pastAuthenticationProcedures = new AuthenticationProcedureList([$firstProcedure]);

        // No exception should be thrown
        $authenticationLoopGuard->assertNotStuckInLoop($serviceProvider, $pastAuthenticationProcedures);
    }
}
