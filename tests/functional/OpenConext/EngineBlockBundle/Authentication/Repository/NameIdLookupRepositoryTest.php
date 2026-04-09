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
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;
use OpenConext\EngineBlockBundle\Authentication\Repository\SamlPersistentIdRepository;
use OpenConext\EngineBlockBundle\Authentication\Repository\ServiceProviderUuidRepository;
use OpenConext\EngineBlockBundle\Authentication\Repository\UserRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class NameIdLookupRepositoryTest extends KernelTestCase
{
    protected function tearDown(): void
    {
        $this->clearFixtures();
        parent::tearDown();
        restore_exception_handler();
    }

    #[Group('Repository')]
    #[Group('NameIdLookup')]
    #[Test]
    public function find_by_collab_person_uuid_returns_the_correct_user(): void
    {
        $userUuid      = Uuid::uuid4()->toString();
        $collabPersonId = 'urn:collab:person:example.edu:' . uniqid();

        $this->insertUser($collabPersonId, $userUuid);

        $repo = self::getContainer()->get(UserRepository::class);
        $user = $repo->findByCollabPersonUuid(new CollabPersonUuid($userUuid));

        $this->assertNotNull($user);
        $this->assertSame($collabPersonId, $user->collabPersonId->getCollabPersonId());
        $this->assertSame($userUuid, $user->collabPersonUuid->getUuid());
    }

    #[Group('Repository')]
    #[Group('NameIdLookup')]
    #[Test]
    public function find_by_collab_person_uuid_returns_null_when_uuid_is_unknown(): void
    {
        $repo = self::getContainer()->get(UserRepository::class);
        $user = $repo->findByCollabPersonUuid(new CollabPersonUuid('00000000-0000-0000-0000-000000000000'));

        $this->assertNull($user);
    }

    #[Group('Repository')]
    #[Group('NameIdLookup')]
    #[Test]
    public function find_uuid_by_entity_id_returns_the_uuid_for_a_known_sp(): void
    {
        $spUuid     = Uuid::uuid4()->toString();
        $spEntityId = 'https://sp-' . uniqid() . '.example.com/';

        $this->insertServiceProvider($spUuid, $spEntityId);

        $repo = self::getContainer()->get(ServiceProviderUuidRepository::class);
        $uuid = $repo->findUuidByEntityId($spEntityId);

        $this->assertSame($spUuid, $uuid);
    }

    #[Group('Repository')]
    #[Group('NameIdLookup')]
    #[Test]
    public function find_uuid_by_entity_id_returns_null_when_sp_is_unknown(): void
    {
        $repo = self::getContainer()->get(ServiceProviderUuidRepository::class);
        $uuid = $repo->findUuidByEntityId('https://unknown-sp.example.com/');

        $this->assertNull($uuid);
    }

    #[Group('Repository')]
    #[Group('NameIdLookup')]
    #[Test]
    public function find_by_user_and_sp_uuid_returns_entry_when_persistent_id_exists(): void
    {
        $userUuid     = Uuid::uuid4()->toString();
        $spUuid       = Uuid::uuid4()->toString();
        $persistentId = sha1('COIN:' . $userUuid . $spUuid);

        $this->insertPersistentId($persistentId, $userUuid, $spUuid);

        $repo  = self::getContainer()->get(SamlPersistentIdRepository::class);
        $entry = $repo->findByUserAndSpUuid($userUuid, $spUuid);

        $this->assertNotNull($entry);
        $this->assertSame($persistentId, $entry->persistentId);
        $this->assertSame($userUuid, $entry->userUuid);
        $this->assertSame($spUuid, $entry->serviceProviderUuid);
    }

    #[Group('Repository')]
    #[Group('NameIdLookup')]
    #[Test]
    public function find_by_user_and_sp_uuid_returns_null_when_no_persistent_id_stored(): void
    {
        $repo  = self::getContainer()->get(SamlPersistentIdRepository::class);
        $entry = $repo->findByUserAndSpUuid(Uuid::uuid4()->toString(), Uuid::uuid4()->toString());

        $this->assertNull($entry);
    }

    private function insertUser(string $collabPersonId, string $uuid): void
    {
        $qb = $this->connection()->createQueryBuilder();
        assert($qb instanceof QueryBuilder);
        $qb->insert('user')
            ->values(['collab_person_id' => ':collab_person_id', 'uuid' => ':uuid'])
            ->setParameters(['collab_person_id' => $collabPersonId, 'uuid' => $uuid])
            ->executeStatement();
    }

    private function insertServiceProvider(string $uuid, string $entityId): void
    {
        $qb = $this->connection()->createQueryBuilder();
        assert($qb instanceof QueryBuilder);
        $qb->insert('service_provider_uuid')
            ->values(['uuid' => ':uuid', 'service_provider_entity_id' => ':entity_id'])
            ->setParameters(['uuid' => $uuid, 'entity_id' => $entityId])
            ->executeStatement();
    }

    private function insertPersistentId(string $persistentId, string $userUuid, string $spUuid): void
    {
        $qb = $this->connection()->createQueryBuilder();
        assert($qb instanceof QueryBuilder);
        $qb->insert('saml_persistent_id')
            ->values([
                'persistent_id'         => ':persistent_id',
                'user_uuid'             => ':user_uuid',
                'service_provider_uuid' => ':sp_uuid',
            ])
            ->setParameters([
                'persistent_id' => $persistentId,
                'user_uuid'     => $userUuid,
                'sp_uuid'       => $spUuid,
            ])
            ->executeStatement();
    }

    private function clearFixtures(): void
    {
        $conn = $this->connection();
        $conn->executeStatement('DELETE FROM saml_persistent_id');
        $conn->executeStatement('DELETE FROM service_provider_uuid');
        $conn->executeStatement('DELETE FROM user');
    }

    private function connection(): Connection
    {
        return self::getContainer()->get('doctrine')->getConnection();
    }
}
