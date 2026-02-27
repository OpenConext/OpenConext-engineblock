<?php

/**
 * Copyright 2026 SURFnet B.V.
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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use OpenConext\EngineBlockBundle\Authentication\Repository\DbalConsentRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests that real consent repository operations work correctly with native prepared statements,
 * and that SQL injection via SP entity ID parameters is neutralized.
 *
 * Background: SAML SP entity IDs are arbitrary URIs submitted by external parties. They flow
 * directly into consent queries as parameters (e.g. in deleteOneFor). If injection were possible,
 * an attacker controlling an SP registration could craft an entity ID to wipe all consent records
 * for any user during a revoke-consent operation.
 *
 * @see https://github.com/OpenConext/OpenConext-engineblock/issues/1859
 */
final class ConsentRepositoryInjectionTest extends WebTestCase
{
    private Connection $connection;
    private DbalConsentRepository $consentRepository;

    protected function setUp(): void
    {
        self::createClient();
        $container = self::getContainer();
        $this->connection = $container->get('doctrine')->getConnection();
        $this->consentRepository = $container->get(DbalConsentRepository::class);
        $this->clearConsentFixtures();
    }

    protected function tearDown(): void
    {
        $this->clearConsentFixtures();
        parent::tearDown();
    }

    /**
     * Normal consent operations must work correctly with native prepared statements.
     * Verifies the connection is actually functional after disabling PDO emulation.
     */
    #[Test]
    #[Group('Security')]
    public function consent_operations_work_correctly_with_native_prepared_statements(): void
    {
        $userId = 'urn:collab:person:surfnet.nl:test-user';
        $spEntityId = 'https://sp.example.org/saml/metadata';

        $this->insertConsent($userId, $spEntityId);

        // findAllFor must find the consent we just inserted
        $consents = $this->consentRepository->findAllFor($userId);
        $this->assertCount(1, $consents);
        $this->assertSame($spEntityId, $consents[0]->getServiceProviderEntityId());

        // deleteOneFor must soft-delete only the target consent
        $deleted = $this->consentRepository->deleteOneFor($userId, $spEntityId);
        $this->assertTrue($deleted, 'deleteOneFor should return true when a row is affected');

        // After soft-delete the consent must be gone from findAllFor
        $consentsAfter = $this->consentRepository->findAllFor($userId);
        $this->assertCount(0, $consentsAfter);
    }

    /**
     * SP entity IDs are controlled by external parties. An attacker could register an SP with a
     * malicious entity ID and trigger a remove-consent flow to attempt to wipe consent records
     * for other users/services.
     *
     * With native prepared statements the injection string is treated as a literal value and
     * does NOT match any other row. The legitimate consent record for a different SP is untouched.
     */
    #[Test]
    #[Group('Security')]
    public function sql_injection_in_sp_entity_id_does_not_affect_other_consent_records(): void
    {
        $userId = 'urn:collab:person:surfnet.nl:victim-user';
        $legitimateSp = 'https://legitimate-sp.example.org/saml/metadata';

        // Insert a consent for the legitimate SP
        $this->insertConsent($userId, $legitimateSp);

        // An attacker crafts a SP entity ID that, in a concatenated query, would match ALL rows:
        //   WHERE service_id = '' OR '1'='1'   → wipes everything
        $maliciousSpEntityId = "' OR '1'='1";

        $deleted = $this->consentRepository->deleteOneFor($userId, $maliciousSpEntityId);

        // With native prepared statements the malicious string is a literal parameter value —
        // no SP with that exact entity ID exists, so 0 rows are affected.
        $this->assertFalse($deleted, 'Injection payload must not match any real row');

        // The legitimate consent record must be completely untouched.
        $remaining = $this->consentRepository->findAllFor($userId);
        $this->assertCount(1, $remaining, 'The legitimate consent must survive the injection attempt');
        $this->assertSame($legitimateSp, $remaining[0]->getServiceProviderEntityId());
    }

    /**
     * Demonstrates WHY parameterized queries are critical.
     *
     * This test shows that the SAME injection payload that is safely neutralized by the
     * DbalConsentRepository WOULD succeed if a developer accidentally built the query
     * via string concatenation instead of parameter binding.
     *
     * The test asserts the WRONG (injectable) behaviour to document the attack clearly:
     * a concatenated query returns rows it should not, wiping unintended consent records.
     */
    #[Test]
    #[Group('Security')]
    public function raw_string_concatenation_is_vulnerable_to_the_same_injection(): void
    {
        $userId = 'urn:collab:person:surfnet.nl:victim-user';
        $legitimateSp = 'https://legitimate-sp.example.org/saml/metadata';

        $this->insertConsent($userId, $legitimateSp);

        $maliciousSpEntityId = "' OR '1'='1";

        // Simulate a developer mistake: building SQL via concatenation (no parameters).
        // The resulting query becomes:
        //   UPDATE consent SET deleted_at = NOW()
        //   WHERE hashed_user_id = '<hash>'
        //   AND   service_id = '' OR '1'='1'
        //   AND   deleted_at IS NULL
        //
        // The OR '1'='1' causes the WHERE clause to always be true → ALL consents are soft-deleted.
        $vulnerableRowCount = $this->connection->executeStatement(
            "UPDATE consent SET deleted_at = NOW()"
            . " WHERE hashed_user_id = '" . sha1($userId) . "'"
            . " AND service_id = '" . $maliciousSpEntityId . "'"
            . " AND deleted_at IS NULL"
        );

        // The concatenated query matches the legitimate row even though we asked for a different SP.
        // This proves injection works WITHOUT parameterization.
        $this->assertGreaterThan(
            0,
            $vulnerableRowCount,
            'The concatenated (vulnerable) query must have matched and soft-deleted rows it should not have'
        );

        // The legitimate consent is now gone — collateral damage from the injection.
        $remaining = $this->consentRepository->findAllFor($userId);
        $this->assertCount(0, $remaining, 'Injection via concatenation wiped the legitimate consent');
    }

    private function insertConsent(string $userId, string $spEntityId): void
    {
        $qb = $this->connection->createQueryBuilder();
        assert($qb instanceof QueryBuilder);
        $qb->insert('consent')
            ->values([
                'hashed_user_id' => ':hashed_user_id',
                'service_id'     => ':service_id',
                'attribute'      => ':attribute',
                'consent_type'   => ':consent_type',
                'consent_date'   => ':consent_date',
                'deleted_at'     => ':deleted_at',
            ])
            ->setParameters([
                'hashed_user_id' => sha1($userId),
                'service_id'     => $spEntityId,
                'attribute'      => sha1('some-attribute-hash'),
                'consent_type'   => 'explicit',
                'consent_date'   => '2024-01-01 00:00:00',
                'deleted_at'     => '0000-00-00 00:00:00',
            ])
            ->executeStatement();
    }

    private function clearConsentFixtures(): void
    {
        $qb = $this->connection->createQueryBuilder();
        assert($qb instanceof QueryBuilder);
        $qb->delete('consent')->executeStatement();
    }
}
