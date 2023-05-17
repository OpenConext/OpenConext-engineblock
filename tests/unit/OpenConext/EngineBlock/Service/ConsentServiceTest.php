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
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonUuid;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ConsentServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var ConsentRepository
     */
    private $consentRepository;

    /**
     * @var MetadataService
     */
    private $metadataService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        $this->consentRepository = m::mock(ConsentRepository::class);
        $this->metadataService = m::mock(MetadataServiceInterface::class);
        $this->logger = m::mock(LoggerInterface::class);

        $this->user = new User(
            new CollabPersonId('urn:collab:person:test'),
            new CollabPersonUuid('550e8400-e29b-41d4-a716-446655440000')
        );
    }

    /**
     * @test
     * @group EngineBlock
     * @group Consent
     */
    public function remove_consent_by_collab_person_id_and_sp_entity_id()
    {
        $this->consentRepository->shouldReceive('deleteOneFor')
            ->with($this->user->getCollabPersonId()->getCollabPersonId(), 'https://sp1.example.org');

        $service = new ConsentService(
            $this->consentRepository,
            $this->metadataService,
            $this->logger
        );

        $service->deleteOneConsentFor(new CollabPersonId('urn:collab:person:test'), 'https://sp1.example.org');
    }
}
