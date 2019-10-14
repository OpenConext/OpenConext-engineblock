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
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntityType;
use PHPUnit\Framework\TestCase;

class AuthenticationProcedureTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     * @group AuthenticationState
     */
    public function an_authentication_procedure_is_started_on_behalf_of_the_same_service_provider_as_given()
    {
        $serviceProvider = new Entity(new EntityId('my.service-provider.example'), EntityType::SP());
        $authenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);

        $sameServiceProvider = $authenticationProcedure->isOnBehalfOf($serviceProvider);

        $this->assertTrue(
            $sameServiceProvider,
            'The authentication procedure has not been started on behalf of the same service provider as the one given '
            . 'but it should'
        );
    }

    /**
     * @test
     * @group AuthenticationState
     */
    public function an_authentication_procedure_is_started_on_behalf_of_a_different_service_provider_as_given()
    {
        $serviceProvider = new Entity(new EntityId('my.service-provider.example'), EntityType::SP());
        $differentServiceProvider = new Entity(new EntityId('other.service-provider.example'), EntityType::SP());
        $authenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);

        $sameServiceProvider = $authenticationProcedure->isOnBehalfOf($differentServiceProvider);

        $this->assertFalse(
            $sameServiceProvider,
            'The authentication procedure has been started on behalf of the same service provider as the one given '
            . 'but it should not'
        );
    }

    /**
     * @test
     * @group AuthenticationState
     */
    public function an_authentication_procedure_is_authenticated_at_the_same_identity_provider_as_given()
    {
        $serviceProvider = new Entity(new EntityId('my.service-provider.example'), EntityType::SP());
        $authenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);

        $identityProvider = new Entity(new EntityId('my.identity-provider.example'), EntityType::IdP());
        $authenticationProcedure->authenticatedAt($identityProvider);

        $sameIdentityProvider = $authenticationProcedure->hasBeenAuthenticatedAt($identityProvider);

        $this->assertTrue(
            $sameIdentityProvider,
            'The authentication procedure has not been authenticated at the same identity provider as the one given '
            .'but it should'
        );
    }

    /**
     * @test
     * @group AuthenticationState
     */
    public function an_authentication_procedure_is_authenticated_at_the_different_identity_provider_as_given()
    {
        $serviceProvider = new Entity(new EntityId('my.service-provider.example'), EntityType::SP());
        $authenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);

        $identityProvider = new Entity(new EntityId('my.identity-provider.example'), EntityType::IdP());
        $differentIdentityProvider = new Entity(new EntityId('other.identity-provider.example'), EntityType::IdP());
        $authenticationProcedure->authenticatedAt($identityProvider);

        $sameIdentityProvider = $authenticationProcedure->hasBeenAuthenticatedAt($differentIdentityProvider);

        $this->assertFalse(
            $sameIdentityProvider,
            'The authentication procedure has been authenticated at the same identity provider as the one given '
            .'but it should not'
        );
    }

    /**
     * @test
     * @group AuthenticationState
     */
    public function an_authentication_procedure_is_not_completed_at_all()
    {
        $serviceProvider = new Entity(new EntityId('my.service-provider.example'), EntityType::SP());
        $authenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);

        $identityProvider = new Entity(new EntityId('my.identity-provider.example'), EntityType::IdP());
        $authenticationProcedure->authenticatedAt($identityProvider);

        $hasBeenCompleted = $authenticationProcedure->isCompletedAfter(new DateTimeImmutable('01-01-1970'));

        $this->assertFalse(
            $hasBeenCompleted,
            'The authentication procedure has been completed, but it should not have been'
        );
    }

    /**
     * @test
     * @group AuthenticationState
     */
    public function an_authentication_procedure_is_not_completed_after_the_time_given()
    {
        $serviceProvider = new Entity(new EntityId('my.service-provider.example'), EntityType::SP());
        $authenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);

        $identityProvider = new Entity(new EntityId('my.identity-provider.example'), EntityType::IdP());
        $authenticationProcedure->authenticatedAt($identityProvider);

        $timeOfCompletion = new DateTimeImmutable('01-01-1970');
        $laterTime = $timeOfCompletion->modify('+1 day');
        $authenticationProcedure->completeOn($timeOfCompletion);

        $completedAfterGivenTime = $authenticationProcedure->isCompletedAfter($laterTime);

        $this->assertFalse(
            $completedAfterGivenTime,
            'The authentication procedure has been completed after given time, but it should not have been'
        );
    }

    /**
     * @test
     * @group AuthenticationState
     */
    public function an_authentication_procedure_is_completed_after_the_time_given()
    {
        $serviceProvider = new Entity(new EntityId('my.service-provider.example'), EntityType::SP());
        $authenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);

        $identityProvider = new Entity(new EntityId('my.identity-provider.example'), EntityType::IdP());
        $authenticationProcedure->authenticatedAt($identityProvider);

        $timeOfCompletion = new DateTimeImmutable('01-01-1970');
        $earlierTime = $timeOfCompletion->modify('-1 day');
        $authenticationProcedure->completeOn($timeOfCompletion);

        $completedAfterGivenTime = $authenticationProcedure->isCompletedAfter($earlierTime);

        $this->assertTrue(
            $completedAfterGivenTime,
            'The authentication procedure has not been completed after given time, but it should have been'
        );
    }

    /**
     * @test
     * @group AuthenticationState
     */
    public function an_authentication_procedure_equals_the_same_authentication_procedure()
    {
        $serviceProvider  = new Entity(new EntityId('some.service-provider.example'), EntityType::SP());
        $identityProvider = new Entity(new EntityId('some.identity-provider.example'), EntityType::IdP());
        $dateOfCompletion  = new DateTimeImmutable('01-01-1970');

        $authenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);
        $authenticationProcedure->authenticatedAt($identityProvider);
        $authenticationProcedure->completeOn($dateOfCompletion);

        $sameAuthenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);
        $sameAuthenticationProcedure->authenticatedAt($identityProvider);
        $sameAuthenticationProcedure->completeOn($dateOfCompletion);

        $authenticationProceduresAreEqual = $authenticationProcedure->equals($sameAuthenticationProcedure);

        $this->assertTrue($authenticationProceduresAreEqual, 'The same authentication procedures should be equal');
    }

    /**
     * @test
     * @group AuthenticationState
     */
    public function an_authentication_procedure_does_not_equal_an_authentication_procedure_with_another_service_provider()
    {
        $serviceProvider  = new Entity(new EntityId('some.service-provider.example'), EntityType::SP());
        $identityProvider = new Entity(new EntityId('some.identity-provider.example'), EntityType::IdP());
        $dateOfCompletion  = new DateTimeImmutable('01-01-1970');

        $otherServiceProvider = new Entity(new EntityId('other.service-provider.example'), EntityType::SP());

        $authenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);
        $authenticationProcedure->authenticatedAt($identityProvider);
        $authenticationProcedure->completeOn($dateOfCompletion);

        $differentAuthenticationProcedure = AuthenticationProcedure::onBehalfOf($otherServiceProvider);
        $differentAuthenticationProcedure->authenticatedAt($identityProvider);
        $differentAuthenticationProcedure->completeOn($dateOfCompletion);

        $authenticationProceduresAreEqual = $authenticationProcedure->equals($differentAuthenticationProcedure);

        $this->assertFalse(
            $authenticationProceduresAreEqual,
            'Authentication procedures with different service providers should not be equal'
        );
    }

    /**
     * @test
     * @group AuthenticationState
     */
    public function an_authentication_procedure_does_not_equal_an_authentication_procedure_without_an_identity_provider()
    {
        $serviceProvider  = new Entity(new EntityId('some.service-provider.example'), EntityType::SP());
        $identityProvider = new Entity(new EntityId('some.identity-provider.example'), EntityType::IdP());
        $dateOfCompletion  = new DateTimeImmutable('01-01-1970');

        $authenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);
        $authenticationProcedure->authenticatedAt($identityProvider);
        $authenticationProcedure->completeOn($dateOfCompletion);

        $differentAuthenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);
        $differentAuthenticationProcedure->completeOn($dateOfCompletion);

        $authenticationProceduresAreEqual = $authenticationProcedure->equals($differentAuthenticationProcedure);

        $this->assertFalse(
            $authenticationProceduresAreEqual,
            'The authentication procedures with an identity provider '
            . 'should not equal an authentication provider without one'
        );
    }

    /**
     * @test
     * @group AuthenticationState
     */
    public function an_authentication_procedure_does_not_equal_an_authentication_procedure_with_another_identity_provider()
    {
        $serviceProvider  = new Entity(new EntityId('some.service-provider.example'), EntityType::SP());
        $identityProvider = new Entity(new EntityId('some.identity-provider.example'), EntityType::IdP());
        $dateOfCompletion  = new DateTimeImmutable('01-01-1970');

        $otherIdentityProvider = new Entity(new EntityId('other.service-provider.example'), EntityType::IdP());

        $authenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);
        $authenticationProcedure->authenticatedAt($identityProvider);
        $authenticationProcedure->completeOn($dateOfCompletion);

        $differentAuthenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);
        $differentAuthenticationProcedure->authenticatedAt($otherIdentityProvider);
        $differentAuthenticationProcedure->completeOn($dateOfCompletion);

        $authenticationProceduresAreEqual = $authenticationProcedure->equals($differentAuthenticationProcedure);

        $this->assertFalse(
            $authenticationProceduresAreEqual,
            'Authentication procedures with different identity providers should not be equal'
        );
    }

    /**
     * @test
     * @group AuthenticationState
     */
    public function an_authentication_procedure_does_not_equal_an_authentication_procedure_with_another_date_of_completion()
    {
        $serviceProvider  = new Entity(new EntityId('some.service-provider.example'), EntityType::SP());
        $identityProvider = new Entity(new EntityId('some.identity-provider.example'), EntityType::IdP());
        $dateOfCompletion = new DateTimeImmutable('01-01-1970');

        $otherDateOfCompletion = new DateTimeImmutable('01-01-1980');

        $authenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);
        $authenticationProcedure->authenticatedAt($identityProvider);
        $authenticationProcedure->completeOn($dateOfCompletion);

        $differentAuthenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);
        $differentAuthenticationProcedure->authenticatedAt($identityProvider);
        $differentAuthenticationProcedure->completeOn($otherDateOfCompletion);
        $authenticationProceduresAreEqual = $authenticationProcedure->equals($differentAuthenticationProcedure);

        $this->assertFalse(
            $authenticationProceduresAreEqual,
            'Authentication procedures with different completed on dates should not be equal'
        );
    }

    /**
     * @test
     * @group AuthenticationState
     */
    public function an_authentication_procedure_does_not_equal_an_authentication_procedure_without_a_date_of_completion()
    {
        $serviceProvider  = new Entity(new EntityId('some.service-provider.example'), EntityType::SP());
        $identityProvider = new Entity(new EntityId('some.identity-provider.example'), EntityType::IdP());
        $dateOfCompletion  = new DateTimeImmutable('01-01-1970');

        $authenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);
        $authenticationProcedure->authenticatedAt($identityProvider);

        $differentAuthenticationProcedure = AuthenticationProcedure::onBehalfOf($serviceProvider);
        $differentAuthenticationProcedure->authenticatedAt($identityProvider);
        $differentAuthenticationProcedure->completeOn($dateOfCompletion);

        $authenticationProceduresAreEqual = $authenticationProcedure->equals($differentAuthenticationProcedure);

        $this->assertFalse(
            $authenticationProceduresAreEqual,
            'An authentication procedure with a completed on date should not equal without one'
        );
    }
}
