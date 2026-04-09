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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use OpenConext\EngineBlock\Authentication\Repository\ConsentRepository;
use OpenConext\EngineBlock\Authentication\Value\ConsentHashQuery;
use OpenConext\EngineBlock\Authentication\Value\ConsentStoreParameters;
use OpenConext\EngineBlock\Authentication\Value\ConsentType;
use OpenConext\EngineBlock\Authentication\Value\ConsentUpdateParameters;
use OpenConext\EngineBlock\Authentication\Value\ConsentVersion;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Service\Consent\ConsentHashService;
use OpenConext\EngineBlockBundle\Authentication\Repository\DbalConsentRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConsentIntegrationTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private $consent;
    /**
     * @var ConsentHashService
     */
    private $consentService;
    /**
     * @var DbalConsentRepository
     */
    private $consentRepository;
    /**
     * @var EngineBlock_Saml2_ResponseAnnotationDecorator|MockInterface
     */
    private $response;

    public function setup(): void
    {
        Mockery::getConfiguration()->setDefaultMatcher(ConsentHashQuery::class, \Mockery\Matcher\IsEqual::class);
        Mockery::getConfiguration()->setDefaultMatcher(ConsentStoreParameters::class, \Mockery\Matcher\IsEqual::class);
        Mockery::getConfiguration()->setDefaultMatcher(ConsentUpdateParameters::class, \Mockery\Matcher\IsEqual::class);

        $this->response = Mockery::mock(EngineBlock_Saml2_ResponseAnnotationDecorator::class);
        $this->consentRepository = Mockery::mock(ConsentRepository::class);

        $this->consentService = new ConsentHashService($this->consentRepository);
        $this->consent = new EngineBlock_Corto_Model_Consent(
            true,
            $this->response,
            [],
            false,
            true,
            $this->consentService
        );
    }

    #[DataProvider('consentTypeProvider')]
    public function test_no_previous_consent_given($consentType)
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        $this->response->shouldReceive('getNameIdValue')
            ->once()
            ->andReturn('collab:person:id:org-a:joe-a');
        // No consent is given previously
        $this->consentRepository
            ->shouldReceive('hasConsentHash')
            ->once()
            ->andReturn(ConsentVersion::NotGiven);
        switch ($consentType) {
            case ConsentType::Explicit:
                $this->assertFalse($this->consent->explicitConsentWasGivenFor($serviceProvider)->given());
                break;
            case ConsentType::Implicit:
                $this->assertFalse($this->consent->implicitConsentWasGivenFor($serviceProvider)->given());
                break;
        }
    }

    #[DataProvider('consentTypeProvider')]
    public function test_unstable_previous_consent_given($consentType)
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        $this->response->shouldReceive('getNameIdValue')
            ->once()
            ->andReturn('collab:person:id:org-a:joe-a');
        // Stable consent is not yet stored
        $this->consentRepository
            ->shouldReceive('hasConsentHash')
            ->with(new ConsentHashQuery(
                hashedUserId: '0e54805079c56c2b1c1197a760af86ac337b7bac',
                serviceId: 'service-provider-entity-id',
                attributeHash: '8739602554c7f3241958e3cc9b57fdecb474d508',
                attributeStableHash: '8739602554c7f3241958e3cc9b57fdecb474d508',
                consentType: $consentType->value,
            ))
            ->once()
            ->andReturn(ConsentVersion::Unstable);

        switch ($consentType) {
            case ConsentType::Explicit:
                $this->assertTrue($this->consent->explicitConsentWasGivenFor($serviceProvider)->given());
                break;
            case ConsentType::Implicit:
                $this->assertTrue($this->consent->implicitConsentWasGivenFor($serviceProvider)->given());
                break;
        }
    }

    #[DataProvider('consentTypeProvider')]
    public function test_stable_consent_given($consentType)
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        $this->response->shouldReceive('getNameIdValue')
            ->once()
            ->andReturn('collab:person:id:org-a:joe-a');
        // Stable consent is not yet stored
        $this->consentRepository
            ->shouldReceive('hasConsentHash')
            ->with(new ConsentHashQuery(
                hashedUserId: '0e54805079c56c2b1c1197a760af86ac337b7bac',
                serviceId: 'service-provider-entity-id',
                attributeHash: '8739602554c7f3241958e3cc9b57fdecb474d508',
                attributeStableHash: '8739602554c7f3241958e3cc9b57fdecb474d508',
                consentType: $consentType->value,
            ))
            ->once()
            ->andReturn(ConsentVersion::Stable);

        switch ($consentType) {
            case ConsentType::Explicit:
                $this->assertTrue($this->consent->explicitConsentWasGivenFor($serviceProvider)->given());
                break;
            case ConsentType::Implicit:
                $this->assertTrue($this->consent->implicitConsentWasGivenFor($serviceProvider)->given());
                break;
        }
    }

    /**
     * New consent always stores both the stable and legacy hashes so that old
     * instances can still find the consent record during a rolling deploy, and
     * so the legacy column is never wiped prematurely.
     */
    #[DataProvider('consentTypeProvider')]
    public function test_give_consent_stores_both_hashes($consentType)
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        $this->response->shouldReceive('getNameIdValue')
            ->once()
            ->andReturn('collab:person:id:org-a:joe-a');
        $this->consentRepository
            ->shouldReceive('storeConsentHash')
            ->once()
            ->with(new ConsentStoreParameters(
                hashedUserId: '0e54805079c56c2b1c1197a760af86ac337b7bac',
                serviceId: 'service-provider-entity-id',
                attributeStableHash: '8739602554c7f3241958e3cc9b57fdecb474d508',
                consentType: $consentType->value,
                attributeHash: '8739602554c7f3241958e3cc9b57fdecb474d508',
            ))
            ->andReturn(true);

        switch ($consentType) {
            case ConsentType::Explicit:
                $this->assertTrue($this->consent->giveExplicitConsentFor($serviceProvider));
                break;
            case ConsentType::Implicit:
                $this->assertTrue($this->consent->giveImplicitConsentFor($serviceProvider));
                break;
        }
    }

    /**
     * Upgrading an unstable consent always preserves the legacy `attribute` column
     * so that old instances keep working during a rolling deploy.
     */
    #[DataProvider('consentTypeProvider')]
    public function test_upgrade_preserves_legacy_hash($consentType)
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        $this->response->shouldReceive('getNameIdValue')
            ->once()
            ->andReturn('collab:person:id:org-a:joe-a');
        $this->consentRepository
            ->shouldReceive('updateConsentHash')
            ->once()
            ->with(new ConsentUpdateParameters(
                attributeStableHash: '8739602554c7f3241958e3cc9b57fdecb474d508',
                attributeHash: '8739602554c7f3241958e3cc9b57fdecb474d508',
                hashedUserId: '0e54805079c56c2b1c1197a760af86ac337b7bac',
                serviceId: 'service-provider-entity-id',
                consentType: $consentType->value,
            ))
            ->andReturn(true);

        $this->assertNull($this->consent->upgradeAttributeHashFor($serviceProvider, $consentType, ConsentVersion::Unstable));
    }

    #[DataProvider('consentTypeProvider')]
    public function test_upgrade_to_stable_consent_not_applied_when_stable($consentType)
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        // No DB calls expected — stable consent does not trigger an update
        $this->consentRepository->shouldNotReceive('hasConsentHash');
        $this->consentRepository->shouldNotReceive('storeConsentHash');
        $this->consentRepository->shouldNotReceive('updateConsentHash');

        // Pass the pre-fetched ConsentVersion (stable) — no second DB query is made, no update triggered
        $this->assertNull($this->consent->upgradeAttributeHashFor($serviceProvider, $consentType, ConsentVersion::Stable));
    }

    #[DataProvider('consentTypeProvider')]
    public function test_upgrade_not_applied_when_no_consent_given($consentType)
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        // No DB calls expected — no consent means nothing to upgrade
        $this->consentRepository->shouldNotReceive('hasConsentHash');
        $this->consentRepository->shouldNotReceive('updateConsentHash');

        // Pass the pre-fetched ConsentVersion (notGiven) — no update should be triggered
        $this->assertNull($this->consent->upgradeAttributeHashFor($serviceProvider, $consentType, ConsentVersion::NotGiven));
    }

    #[DataProvider('consentTypeProvider')]
    public function test_upgrade_continues_gracefully_when_attributes_changed($consentType)
    {
        $serviceProvider = new ServiceProvider("service-provider-entity-id");
        $this->response->shouldReceive('getNameIdValue')
            ->once()
            ->andReturn('collab:person:id:org-a:joe-a');
        // But the UPDATE matches 0 rows (attributes changed since consent was given)
        $this->consentRepository
            ->shouldReceive('updateConsentHash')
            ->once()
            ->andReturn(false);

        // Must not throw; the warning is logged inside the repository
        // Pass the pre-fetched ConsentVersion (unstable) — no second DB query is made
        $this->assertNull($this->consent->upgradeAttributeHashFor($serviceProvider, $consentType, ConsentVersion::Unstable));
    }

    public function test_store_consent_hash_sql_resets_deleted_at_on_duplicate(): void
    {
        // The storeConsentHash SQL must reset deleted_at='0000-00-00 00:00:00' in the
        // ON DUPLICATE KEY UPDATE clause so soft-deleted rows become active again.
        // We verify this by checking the SQL string directly.
        new \ReflectionClass(DbalConsentRepository::class);

        // Read the SQL from the source to verify it contains the deleted_at reset
        // This is a documentation test — if the SQL is refactored, update it here too.
        $source = file_get_contents(
            __DIR__ . '/../../../../../../src/OpenConext/EngineBlockBundle/Authentication/Repository/DbalConsentRepository.php'
        );
        $this->assertStringContainsString(
            "deleted_at='0000-00-00 00:00:00'",
            $source,
            'ON DUPLICATE KEY UPDATE must reset deleted_at so soft-deleted re-consent rows become active'
        );
    }

    public static function consentTypeProvider(): iterable
    {
        yield [ConsentType::Implicit];
        yield [ConsentType::Explicit];
    }
}
