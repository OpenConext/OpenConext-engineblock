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
use OpenConext\EngineBlock\Authentication\Model\User;
use OpenConext\EngineBlock\Authentication\Repository\ConsentRepository;
use OpenConext\EngineBlock\Authentication\Repository\UserDirectory;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;
use OpenConext\EngineBlockBundle\Authentication\Entity\SamlPersistentId;
use OpenConext\EngineBlockBundle\Authentication\Entity\ServiceProviderUuid;
use OpenConext\EngineBlockBundle\Authentication\Repository\SamlPersistentIdRepository;
use OpenConext\EngineBlockBundle\Authentication\Repository\ServiceProviderUuidRepository;
use PHPUnit\Framework\TestCase;

class DeprovisionServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var ConsentRepository
     */
    private $consentRepository;

    /**
     * @var UserDirectory
     */
    private $userDirectory;

    /**
     * @var SamlPersistentIdRepository
     */
    private $persistentIdRepository;

    /**
     * @var ServiceProviderUuidRepository
     */
    private $serviceProviderUuidRepository;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        $this->consentRepository = m::mock(ConsentRepository::class);
        $this->userDirectory = m::mock(UserDirectory::class);
        $this->persistentIdRepository = m::mock(SamlPersistentIdRepository::class);
        $this->serviceProviderUuidRepository = m::mock(ServiceProviderUuidRepository::class);

        $this->user = new User(
            new CollabPersonId('urn:collab:person:test'),
            new CollabPersonUuid('550e8400-e29b-41d4-a716-446655440000')
        );
    }

    /**
     * @test
     * @group EngineBlock
     * @group Deprovision
     */
    public function read_returns_all_user_data()
    {
        $this->userDirectory->shouldReceive('findUserBy')
            ->andReturn($this->user);

        $persistentId = new SamlPersistentId();
        $persistentId->serviceProviderUuid = '716601c8-67e9-11e8-adc0-fa7ae01bbebc';
        $persistentId->userUuid = $this->user->getCollabPersonUuid()->getUuid();
        $persistentId->persistentId = 'persistent-id';

        $this->persistentIdRepository->shouldReceive('findByUuid')
            ->with($this->user->getCollabPersonUuid())
            ->andReturn([$persistentId]);

        $spUuid = new ServiceProviderUuid();
        $spUuid->serviceProviderEntityId = 'https://example.org/sp';
        $spUuid->uuid = $persistentId->serviceProviderUuid;

        $this->serviceProviderUuidRepository->shouldReceive('findEntityIdByUuid')
            ->with($persistentId->serviceProviderUuid)
            ->andReturn($spUuid->serviceProviderEntityId);

        $this->consentRepository->shouldReceive('findAllFor')
            ->with('urn:collab:person:test')
            ->andReturn([
                ['data' => 'consent1'],
                ['data' => 'consent2'],
                ['data' => 'consent3']
            ]);

        $service = new DeprovisionService(
            $this->consentRepository,
            $this->userDirectory,
            $this->persistentIdRepository,
            $this->serviceProviderUuidRepository
        );

        $result = $service->read(
            new CollabPersonId('urn:collab:person:test')
        );

        $this->assertCount(3, $result);

        $this->assertEquals('user', $result[0]['name']);
        $this->assertEquals('saml_persistent_id', $result[1]['name']);
        $this->assertEquals('consent', $result[2]['name']);

        $this->assertEquals($this->user, $result[0]['value']);

        $this->assertEquals('persistent-id', $result[1]['value'][0]['persistent_id']);
        $this->assertEquals('https://example.org/sp', $result[1]['value'][0]['service_provider_entity_id']);
        $this->assertEquals($this->user->getCollabPersonUuid()->getUuid(), $result[1]['value'][0]['user_uuid']);

        $this->assertCount(3, $result[2]['value']);
        $this->assertEquals(['data' => 'consent1'], $result[2]['value'][0]);
        $this->assertEquals(['data' => 'consent2'], $result[2]['value'][1]);
        $this->assertEquals(['data' => 'consent3'], $result[2]['value'][2]);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Deprovision
     */
    public function read_returns_empty_result_for_unknown_user()
    {
        $this->userDirectory->shouldReceive('findUserBy')
            ->andReturn(null);

        $service = new DeprovisionService(
            $this->consentRepository,
            $this->userDirectory,
            $this->persistentIdRepository,
            $this->serviceProviderUuidRepository
        );

        $result = $service->read(
            new CollabPersonId('urn:collab:person:test')
        );

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Deprovision
     */
    public function read_returns_user_data_without_consent_or_persistent_id()
    {
        $this->userDirectory->shouldReceive('findUserBy')
            ->andReturn($this->user);

        $this->persistentIdRepository->shouldReceive('findByUuid')
            ->with($this->user->getCollabPersonUuid())
            ->andReturn([]);

        $this->consentRepository->shouldReceive('findAllFor')
            ->with('urn:collab:person:test')
            ->andReturn([]);

        $service = new DeprovisionService(
            $this->consentRepository,
            $this->userDirectory,
            $this->persistentIdRepository,
            $this->serviceProviderUuidRepository
        );

        $result = $service->read(
            new CollabPersonId('urn:collab:person:test')
        );

        $this->assertCount(3, $result);

        $this->assertEquals('user', $result[0]['name']);
        $this->assertEquals('saml_persistent_id', $result[1]['name']);
        $this->assertEquals('consent', $result[2]['name']);

        $this->assertEquals($this->user, $result[0]['value']);

        $this->assertIsArray($result[1]['value']);
        $this->assertEmpty($result[1]['value']);

        $this->assertIsArray($result[2]['value']);
        $this->assertEmpty($result[2]['value']);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Deprovision
     */
    public function delete_deprovisions_all_user_data()
    {
        $this->userDirectory->shouldReceive('findUserBy')
            ->andReturn($this->user);

        $this->consentRepository->shouldReceive('deleteAllFor')
            ->with($this->user->getCollabPersonId()->getCollabPersonId());

        $this->persistentIdRepository->shouldReceive('deleteByUuid')
            ->with($this->user->getCollabPersonUuid());

        $this->userDirectory->shouldReceive('removeUserWith')
            ->with($this->user->getCollabPersonId());

        $service = new DeprovisionService(
            $this->consentRepository,
            $this->userDirectory,
            $this->persistentIdRepository,
            $this->serviceProviderUuidRepository
        );

        $service->delete(
            new CollabPersonId('urn:collab:person:test')
        );
    }
}
