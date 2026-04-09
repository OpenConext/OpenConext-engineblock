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

namespace OpenConext\EngineBlock\Service;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;
use OpenConext\EngineBlock\Service\NameIdLookupService;
use OpenConext\EngineBlock\Service\NameIdResult;
use OpenConext\EngineBlock\Service\UserIdentityResult;
use OpenConext\EngineBlockBundle\Authentication\Entity\SamlPersistentId;
use OpenConext\EngineBlockBundle\Authentication\Entity\User;
use OpenConext\EngineBlockBundle\Authentication\Repository\SamlPersistentIdRepository;
use OpenConext\EngineBlockBundle\Authentication\Repository\ServiceProviderUuidRepository;
use OpenConext\EngineBlockBundle\Authentication\Repository\UserRepository;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class NameIdLookupServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private $userRepository;
    private $spUuidRepository;
    private $persistentIdRepository;
    private $logger;
    private NameIdLookupService $service;

    protected function setUp(): void
    {
        $this->userRepository = m::mock(UserRepository::class);
        $this->spUuidRepository = m::mock(ServiceProviderUuidRepository::class);
        $this->persistentIdRepository = m::mock(SamlPersistentIdRepository::class);
        $this->logger = m::mock(LoggerInterface::class);
        $this->logger->shouldIgnoreMissing();

        $this->service = new NameIdLookupService(
            $this->userRepository,
            $this->spUuidRepository,
            $this->persistentIdRepository,
            $this->logger
        );
    }

    #[Group('NameIdLookup')]
    #[Test]
    public function resolve_name_id_returns_null_when_user_is_not_found(): void
    {
        $this->userRepository->shouldReceive('findByCollabPersonId')
            ->once()
            ->andReturn(null);

        $result = $this->service->resolveNameId('example.edu', 'student001', 'https://sp.example.com/');

        $this->assertNull($result);
    }

    #[Group('NameIdLookup')]
    #[Test]
    public function resolve_name_id_returns_null_when_sp_is_not_found(): void
    {
        $userUuid = Uuid::uuid4()->toString();

        $user = new User();
        $user->collabPersonId = new CollabPersonId('urn:collab:person:example.edu:student001');
        $user->collabPersonUuid = new CollabPersonUuid($userUuid);

        $this->userRepository->shouldReceive('findByCollabPersonId')
            ->once()
            ->andReturn($user);

        $this->spUuidRepository->shouldReceive('findUuidByEntityId')
            ->once()
            ->andReturn(null);

        $result = $this->service->resolveNameId('example.edu', 'student001', 'https://sp.example.com/');

        $this->assertNull($result);
    }

    #[Group('NameIdLookup')]
    #[Test]
    public function resolve_name_id_returns_stored_nameid_when_persistent_id_exists(): void
    {
        $userUuid     = Uuid::uuid4()->toString();
        $spUuid       = Uuid::uuid4()->toString();
        $persistentId = sha1('COIN:' . $userUuid . $spUuid);

        $user = new User();
        $user->collabPersonId = new CollabPersonId('urn:collab:person:example.edu:student001');
        $user->collabPersonUuid = new CollabPersonUuid($userUuid);

        $storedEntry = new SamlPersistentId();
        $storedEntry->persistentId = $persistentId;
        $storedEntry->userUuid = $userUuid;
        $storedEntry->serviceProviderUuid = $spUuid;

        $this->userRepository->shouldReceive('findByCollabPersonId')
            ->once()
            ->andReturn($user);

        $this->spUuidRepository->shouldReceive('findUuidByEntityId')
            ->once()
            ->andReturn($spUuid);

        $this->persistentIdRepository->shouldReceive('findByUserAndSpUuid')
            ->once()
            ->with($userUuid, $spUuid)
            ->andReturn($storedEntry);

        $result = $this->service->resolveNameId('example.edu', 'student001', 'https://sp.example.com/');

        $this->assertInstanceOf(NameIdResult::class, $result);
        $this->assertSame($persistentId, $result->nameId);
        $this->assertTrue($result->stored);
    }

    #[Group('NameIdLookup')]
    #[Test]
    public function resolve_name_id_calculates_nameid_when_not_yet_stored(): void
    {
        $userUuid = Uuid::uuid4()->toString();
        $spUuid   = Uuid::uuid4()->toString();

        $user = new User();
        $user->collabPersonId = new CollabPersonId('urn:collab:person:example.edu:student001');
        $user->collabPersonUuid = new CollabPersonUuid($userUuid);

        $this->userRepository->shouldReceive('findByCollabPersonId')
            ->once()
            ->andReturn($user);

        $this->spUuidRepository->shouldReceive('findUuidByEntityId')
            ->once()
            ->andReturn($spUuid);

        $this->persistentIdRepository->shouldReceive('findByUserAndSpUuid')
            ->once()
            ->andReturn(null);

        $result = $this->service->resolveNameId('example.edu', 'student001', 'https://sp.example.com/');

        $this->assertInstanceOf(NameIdResult::class, $result);
        $this->assertSame(sha1('COIN:' . $userUuid . $spUuid), $result->nameId);
        $this->assertFalse($result->stored);
    }

    #[Group('NameIdLookup')]
    #[Test]
    public function resolve_name_id_replaces_at_sign_in_uid_when_building_collab_person_id(): void
    {
        $this->userRepository->shouldReceive('findByCollabPersonId')
            ->once()
            ->with(m::on(function (CollabPersonId $id): bool {
                return $id->getCollabPersonId() === 'urn:collab:person:example.edu:user_example.edu';
            }))
            ->andReturn(null);

        $this->service->resolveNameId('example.edu', 'user@example.edu', 'https://sp.example.com/');
    }

    #[Group('NameIdLookup')]
    #[Test]
    public function resolve_user_identity_returns_null_when_persistent_id_not_found(): void
    {
        $this->persistentIdRepository->shouldReceive('find')
            ->once()
            ->with('abc123')
            ->andReturn(null);

        $result = $this->service->resolveUserIdentity('abc123');

        $this->assertNull($result);
    }

    #[Group('NameIdLookup')]
    #[Test]
    public function resolve_user_identity_returns_user_data_when_persistent_id_found(): void
    {
        $userUuid     = Uuid::uuid4()->toString();
        $spUuid       = Uuid::uuid4()->toString();
        $persistentId = sha1('COIN:' . $userUuid . $spUuid);

        $storedEntry = new SamlPersistentId();
        $storedEntry->persistentId = $persistentId;
        $storedEntry->userUuid = $userUuid;
        $storedEntry->serviceProviderUuid = $spUuid;

        $user = new User();
        $user->collabPersonId = new CollabPersonId('urn:collab:person:example.edu:student001');
        $user->collabPersonUuid = new CollabPersonUuid($userUuid);

        $this->persistentIdRepository->shouldReceive('find')
            ->once()
            ->with($persistentId)
            ->andReturn($storedEntry);

        $this->userRepository->shouldReceive('findByCollabPersonUuid')
            ->once()
            ->with(m::on(fn(CollabPersonUuid $u) => $u->getUuid() === $userUuid))
            ->andReturn($user);

        $this->spUuidRepository->shouldReceive('findEntityIdByUuid')
            ->once()
            ->with($spUuid)
            ->andReturn('https://sp.example.com/');

        $result = $this->service->resolveUserIdentity($persistentId);

        $this->assertInstanceOf(UserIdentityResult::class, $result);
        $this->assertSame('example.edu', $result->schacHomeOrganization);
        $this->assertSame('student001', $result->uid);
        $this->assertSame('https://sp.example.com/', $result->spEntityId);
    }

    #[Group('NameIdLookup')]
    #[Test]
    public function resolve_user_identity_returns_null_when_user_record_missing(): void
    {
        $userUuid = Uuid::uuid4()->toString();
        $spUuid   = Uuid::uuid4()->toString();

        $storedEntry = new SamlPersistentId();
        $storedEntry->persistentId = 'abc123';
        $storedEntry->userUuid = $userUuid;
        $storedEntry->serviceProviderUuid = $spUuid;

        $this->persistentIdRepository->shouldReceive('find')
            ->once()
            ->andReturn($storedEntry);

        $this->userRepository->shouldReceive('findByCollabPersonUuid')
            ->once()
            ->andReturn(null);

        $result = $this->service->resolveUserIdentity('abc123');

        $this->assertNull($result);
    }

    #[Group('NameIdLookup')]
    #[Test]
    public function resolve_user_identity_returns_null_when_sp_uuid_record_missing(): void
    {
        $userUuid = Uuid::uuid4()->toString();
        $spUuid   = Uuid::uuid4()->toString();

        $storedEntry = new SamlPersistentId();
        $storedEntry->persistentId = 'abc123';
        $storedEntry->userUuid = $userUuid;
        $storedEntry->serviceProviderUuid = $spUuid;

        $user = new User();
        $user->collabPersonId = new CollabPersonId('urn:collab:person:example.edu:student001');
        $user->collabPersonUuid = new CollabPersonUuid($userUuid);

        $this->persistentIdRepository->shouldReceive('find')
            ->once()
            ->andReturn($storedEntry);

        $this->userRepository->shouldReceive('findByCollabPersonUuid')
            ->once()
            ->andReturn($user);

        $this->spUuidRepository->shouldReceive('findEntityIdByUuid')
            ->once()
            ->with($spUuid)
            ->andReturn(null);

        $this->logger->shouldReceive('warning')->once();

        $result = $this->service->resolveUserIdentity('abc123');

        $this->assertNull($result);
    }
}
