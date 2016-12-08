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
use OpenConext\EngineBlockBundle\Authentication\AuthenticationProcedure;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationProcedureList;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use PHPUnit_Framework_TestCase as TestCase;

class AuthenticationProcedureListTest extends TestCase
{
    /**
     * @test
     * @group AuthenticationState
     */
    public function a_new_list_is_returned_when_an_authentication_procedure_is_added_to_an_authentication_procedures_list()
    {
        $someServiceProvider  = new Entity(new EntityId('some.serviceprovider.example'), EntityType::SP());
        $otherServiceProvider = new Entity(new EntityId('other.serviceprovider.example'), EntityType::SP());

        $someAuthenticationProcedure = AuthenticationProcedure::onBehalfOf($someServiceProvider);
        $otherAuthenticationProcedure = AuthenticationProcedure::onBehalfOf($otherServiceProvider);

        $authenticationProcedureList = new AuthenticationProcedureList([$someAuthenticationProcedure]);
        $newAuthenticationProcedureList = $authenticationProcedureList->add($otherAuthenticationProcedure);


        $this->assertTrue(
            $authenticationProcedureList->contains($someAuthenticationProcedure),
            'The original authentication procedure list should have the original authentication procedure'
        );
        $this->assertTrue(
            $authenticationProcedureList->contains($someAuthenticationProcedure),
            'The new authentication procedure list should have the original authentication procedure'
        );

        $this->assertFalse(
            $authenticationProcedureList->contains($otherAuthenticationProcedure),
            'The original authentication procedure list should not mutate when adding another authentication procedure'
        );
        $this->assertTrue(
            $newAuthenticationProcedureList->contains($otherAuthenticationProcedure),
            'The new authentication procedure list should contain the added another authentication procedure'
        );
    }

    /**
     * @test
     * @group AuthenticationState
     */
    public function an_authentication_procedure_list_can_be_filtered_by_authentications_on_behalf_of_a_given_service_provider_returning_a_new_list()
    {
        $someServiceProvider  = new Entity(new EntityId('some.serviceprovider.example'), EntityType::SP());
        $otherServiceProvider = new Entity(new EntityId('other.serviceprovider.example'), EntityType::SP());

        $someAuthenticationProcedure  = AuthenticationProcedure::onBehalfOf($someServiceProvider);
        $otherAuthenticationProcedure = AuthenticationProcedure::onBehalfOf($otherServiceProvider);

        $authenticationProcedureList = new AuthenticationProcedureList([
            $someAuthenticationProcedure,
            $otherAuthenticationProcedure,
        ]);

        $filteredList = $authenticationProcedureList->findOnBehalfOf($someServiceProvider);

        $this->assertTrue(
            $filteredList->contains($someAuthenticationProcedure),
            'The filtered authentication list should contain the matching authentication procedure'
        );
        $this->assertFalse(
            $filteredList->contains($otherAuthenticationProcedure),
            'The filtered authentication list should not contain the non-matching authentication procedure'
        );
    }

    /**
     * @test
     * @group AuthenticationState
     */
    public function an_authentication_procedure_list_can_be_filtered_by_completed_procedures_since_a_given_time_returning_a_new_list()
    {
        $someServiceProvider  = new Entity(new EntityId('some.serviceprovider.example'), EntityType::SP());
        $otherServiceProvider = new Entity(new EntityId('other.serviceprovider.example'), EntityType::SP());
        $someIdentityProvider = new Entity(new EntityId('some.identityprovider.example'), EntityType::IdP());

        $someAuthenticationProcedure  = AuthenticationProcedure::onBehalfOf($someServiceProvider);
        $someAuthenticationProcedure->authenticateAt($someIdentityProvider);
        $someAuthenticationProcedure->completeOn(new DateTimeImmutable('1999-01-01'));

        $otherAuthenticationProcedure = AuthenticationProcedure::onBehalfOf($otherServiceProvider);
        $otherAuthenticationProcedure->authenticateAt($someIdentityProvider);
        $otherAuthenticationProcedure->completeOn(new DateTimeImmutable('2001-01-01'));

        $authenticationProcedureList = new AuthenticationProcedureList([
            $someAuthenticationProcedure,
            $otherAuthenticationProcedure,
        ]);

        $filteredList = $authenticationProcedureList->findProceduresCompletedAfter(
            new DateTimeImmutable('2000-01-01')
        );

        $this->assertFalse(
            $filteredList->contains($someAuthenticationProcedure),
            'The filtered list should not contain a completed procedure before the given time'
        );
        $this->assertTrue(
            $filteredList->contains($otherAuthenticationProcedure),
            'The filtered list should contain a completed procedure after the given time'
        );
    }
}
