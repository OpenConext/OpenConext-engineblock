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
use OpenConext\EngineBlockBundle\Authentication\AuthenticationProcedure;
use OpenConext\EngineBlockBundle\Authentication\AuthenticationProcedureMap;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use PHPUnit\Framework\TestCase;

class AuthenticationProcedureMapTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[\PHPUnit\Framework\Attributes\Group('AuthenticationState')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function a_new_map_is_returned_when_an_authentication_procedure_is_added_to_an_authentication_procedures_map()
    {
        $someServiceProvider  = new Entity(new EntityId('some.serviceprovider.example'), EntityType::SP());
        $otherServiceProvider = new Entity(new EntityId('other.serviceprovider.example'), EntityType::SP());

        $someAuthenticationProcedure = AuthenticationProcedure::onBehalfOf($someServiceProvider);
        $otherAuthenticationProcedure = AuthenticationProcedure::onBehalfOf($otherServiceProvider);
        $requestId = 'UniqeRequestId';

        $authenticationProcedureMap = new AuthenticationProcedureMap([$someAuthenticationProcedure]);
        $newAuthenticationProcedureMap = $authenticationProcedureMap->add($requestId, $otherAuthenticationProcedure);


        $this->assertTrue(
            $authenticationProcedureMap->contains($someAuthenticationProcedure),
            'The original authentication procedure map should have the original authentication procedure'
        );
        $this->assertTrue(
            $authenticationProcedureMap->contains($someAuthenticationProcedure),
            'The new authentication procedure map should have the original authentication procedure'
        );

        $this->assertFalse(
            $authenticationProcedureMap->contains($otherAuthenticationProcedure),
            'The original authentication procedure map should not mutate when adding another authentication procedure'
        );
        $this->assertTrue(
            $newAuthenticationProcedureMap->contains($otherAuthenticationProcedure),
            'The new authentication procedure map should contain the added another authentication procedure'
        );
    }

    #[\PHPUnit\Framework\Attributes\Group('AuthenticationState')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function an_authentication_procedure_map_can_be_filtered_by_authentications_on_behalf_of_a_given_service_provider_returning_a_new_map()
    {
        $someServiceProvider  = new Entity(new EntityId('some.serviceprovider.example'), EntityType::SP());
        $otherServiceProvider = new Entity(new EntityId('other.serviceprovider.example'), EntityType::SP());

        $someAuthenticationProcedure  = AuthenticationProcedure::onBehalfOf($someServiceProvider);
        $otherAuthenticationProcedure = AuthenticationProcedure::onBehalfOf($otherServiceProvider);

        $authenticationProcedureMap = new AuthenticationProcedureMap([
            $someAuthenticationProcedure,
            $otherAuthenticationProcedure,
        ]);

        $filteredMap = $authenticationProcedureMap->filterOnBehalfOf($someServiceProvider);

        $this->assertTrue(
            $filteredMap->contains($someAuthenticationProcedure),
            'The filtered authentication map should contain the matching authentication procedure'
        );
        $this->assertFalse(
            $filteredMap->contains($otherAuthenticationProcedure),
            'The filtered authentication map should not contain the non-matching authentication procedure'
        );
    }

    #[\PHPUnit\Framework\Attributes\Group('AuthenticationState')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function an_authentication_procedure_map_can_be_filtered_by_completed_procedures_since_a_given_time_returning_a_new_map()
    {
        $someServiceProvider  = new Entity(new EntityId('some.serviceprovider.example'), EntityType::SP());
        $otherServiceProvider = new Entity(new EntityId('other.serviceprovider.example'), EntityType::SP());
        $someIdentityProvider = new Entity(new EntityId('some.identityprovider.example'), EntityType::IdP());

        $someAuthenticationProcedure  = AuthenticationProcedure::onBehalfOf($someServiceProvider);
        $someAuthenticationProcedure->authenticatedAt($someIdentityProvider);
        $someAuthenticationProcedure->completeOn(new DateTimeImmutable('1999-01-01'));

        $otherAuthenticationProcedure = AuthenticationProcedure::onBehalfOf($otherServiceProvider);
        $otherAuthenticationProcedure->authenticatedAt($someIdentityProvider);
        $otherAuthenticationProcedure->completeOn(new DateTimeImmutable('2001-01-01'));

        $authenticationProcedureMap = new AuthenticationProcedureMap([
            $someAuthenticationProcedure,
            $otherAuthenticationProcedure,
        ]);

        $filteredMap = $authenticationProcedureMap->filterProceduresCompletedAfter(
            new DateTimeImmutable('2000-01-01')
        );

        $this->assertFalse(
            $filteredMap->contains($someAuthenticationProcedure),
            'The filtered map should not contain a completed procedure before the given time'
        );
        $this->assertTrue(
            $filteredMap->contains($otherAuthenticationProcedure),
            'The filtered map should contain a completed procedure after the given time'
        );
    }
}
