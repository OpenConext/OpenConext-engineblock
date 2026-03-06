<?php

/**
 * Copyright 2021 Stichting Kennisnet
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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Authentication\Value\ConsentType;
use OpenConext\EngineBlock\Authentication\Value\ConsentVersion;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Service\Consent\ConsentHashServiceInterface;
use PHPUnit\Framework\TestCase;

class EngineBlock_Corto_Model_ConsentTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private $consentDisabled;
    private $consent;
    private $consentService;

    public function setUp(): void
    {
        $mockedResponse = Phake::mock('EngineBlock_Saml2_ResponseAnnotationDecorator');
        Phake::when($mockedResponse)->getNameIdValue()->thenReturn('urn:collab:person:example.org:user1');

        $this->consentService = Mockery::mock(ConsentHashServiceInterface::class);

        $this->consentDisabled = new EngineBlock_Corto_Model_Consent(
            "consent",
            true,
            $mockedResponse,
            [],
            false,
            false,
            $this->consentService
        );

        $this->consent = new EngineBlock_Corto_Model_Consent(
            "consent",
            true,
            $mockedResponse,
            [],
            false,
            true,
            $this->consentService
        );
    }

    public function testConsentDisabledDoesNotWriteToDatabase()
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");

        $this->consentService->shouldNotReceive('storeConsentHash');
        $this->consentService->shouldNotReceive('retrieveConsentHash');
        $this->consentService->shouldNotReceive('updateConsentHash');

        $this->assertTrue($this->consentDisabled->explicitConsentWasGivenFor($serviceProvider));
        $this->assertTrue($this->consentDisabled->implicitConsentWasGivenFor($serviceProvider));
        $this->assertTrue($this->consentDisabled->giveExplicitConsentFor($serviceProvider));
        $this->assertTrue($this->consentDisabled->giveImplicitConsentFor($serviceProvider));
    }

    public function testUpgradeAttributeHashSkippedWhenConsentDisabled()
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");

        $this->consentService->shouldNotReceive('retrieveConsentHash');
        $this->consentService->shouldNotReceive('updateConsentHash');

        $this->consentDisabled->upgradeAttributeHashFor($serviceProvider, ConsentType::TYPE_EXPLICIT);
        $this->consentDisabled->upgradeAttributeHashFor($serviceProvider, ConsentType::TYPE_IMPLICIT);
    }

    public function testConsentWriteToDatabase()
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");

        $this->consentService->shouldReceive('getUnstableAttributesHash')->andReturn(sha1('unstable'));
        $this->consentService->shouldReceive('getStableAttributesHash')->andReturn(sha1('stable'));
        $this->consentService->shouldReceive('retrieveConsentHash')->andReturn(ConsentVersion::stable());
        $this->consentService->shouldReceive('storeConsentHash')->andReturn(true);

        $this->assertTrue($this->consent->explicitConsentWasGivenFor($serviceProvider));
        $this->assertTrue($this->consent->implicitConsentWasGivenFor($serviceProvider));
        $this->assertTrue($this->consent->giveExplicitConsentFor($serviceProvider));
        $this->assertTrue($this->consent->giveImplicitConsentFor($serviceProvider));
    }

    public function testCountTotalConsent()
    {
        // Arrange
        $this->consentService->shouldReceive('countTotalConsent')
            ->with('urn:collab:person:example.org:user1')
            ->once()
            ->andReturn(5);

        // Act + Assert
        $this->assertEquals(5, $this->consent->countTotalConsent());
    }

    public function testConsentUidFromAmPriorToConsentEnabled()
    {
        // When amPriorToConsentEnabled is true the consent UID must come from
        // getOriginalResponse()->getCollabPersonId(), NOT from getNameIdValue().
        $originalResponse = Phake::mock('EngineBlock_Saml2_ResponseAnnotationDecorator');
        Phake::when($originalResponse)->getCollabPersonId()->thenReturn('urn:collab:person:example.org:user-am');

        $mockedResponse = Phake::mock('EngineBlock_Saml2_ResponseAnnotationDecorator');
        Phake::when($mockedResponse)->getOriginalResponse()->thenReturn($originalResponse);

        $consentWithAmPrior = new EngineBlock_Corto_Model_Consent(
            'consent',
            true,
            $mockedResponse,
            [],
            true,  // amPriorToConsentEnabled = true
            true,
            $this->consentService
        );

        $serviceProvider = new ServiceProvider('service-provider-entity-id');

        $this->consentService->shouldReceive('getUnstableAttributesHash')->andReturn(sha1('unstable'));
        $this->consentService->shouldReceive('getStableAttributesHash')->andReturn(sha1('stable'));
        $this->consentService->shouldReceive('retrieveConsentHash')->andReturn(ConsentVersion::stable());

        // Act: trigger a code path that calls _getConsentUid()
        $result = $consentWithAmPrior->explicitConsentWasGivenFor($serviceProvider);

        // Assert: consent check succeeded and the AM-prior uid path was used
        $this->assertTrue($result);
        Phake::verify($originalResponse)->getCollabPersonId();
        Phake::verify($mockedResponse, Phake::never())->getNameIdValue();
    }

    public function testNullNameIdReturnsNoConsentWithoutCallingRepository()
    {
        $mockedResponse = Phake::mock('EngineBlock_Saml2_ResponseAnnotationDecorator');
        Phake::when($mockedResponse)->getNameIdValue()->thenReturn(null);

        $consentWithNullUid = new EngineBlock_Corto_Model_Consent(
            "consent",
            true,
            $mockedResponse,
            [],
            false,
            true,
            $this->consentService
        );

        $serviceProvider = new ServiceProvider("service-provider-entity-id");

        // No DB calls should occur when the NameID is null
        $this->consentService->shouldNotReceive('retrieveConsentHash');
        $this->consentService->shouldNotReceive('storeConsentHash');
        $this->consentService->shouldNotReceive('updateConsentHash');

        // _hasStoredConsent returns notGiven() when UID is null -> consent methods return false
        $this->assertFalse($consentWithNullUid->explicitConsentWasGivenFor($serviceProvider));
        $this->assertFalse($consentWithNullUid->implicitConsentWasGivenFor($serviceProvider));
        // giveConsent returns false when UID is null (_storeConsent returns early)
        $this->assertFalse($consentWithNullUid->giveExplicitConsentFor($serviceProvider));
        $this->assertFalse($consentWithNullUid->giveImplicitConsentFor($serviceProvider));
        // upgradeAttributeHashFor should not throw when UID is null
        $consentWithNullUid->upgradeAttributeHashFor($serviceProvider, ConsentType::TYPE_EXPLICIT);
    }
}
