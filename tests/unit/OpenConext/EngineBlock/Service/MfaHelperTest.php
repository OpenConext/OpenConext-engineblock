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
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\CachedDoctrineMetadataRepository;
use OpenConext\EngineBlock\Metadata\MfaEntity;
use OpenConext\EngineBlock\Metadata\MfaEntityCollection;
use OpenConext\EngineBlock\Metadata\TransparentMfaEntity;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MfaHelperTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private $mfaHelper;

    private $repo;

    public function setUp(): void
    {
        $this->repo = m::mock(CachedDoctrineMetadataRepository::class);
        $logger = m::mock(LoggerInterface::class);
        $logger->shouldIgnoreMissing();
        $this->mfaHelper = new MfaHelper($logger, $this->repo);
    }

    /**
     * @test
     * @group EngineBlock
     */
    public function happy_flow()
    {
        $spEntityId = 'arbitrarySpEntityId';
        $idpEntityId = 'arbitraryIdPEntityId';
        $this->repo->shouldReceive('findIdentityProviderByEntityId')->with($idpEntityId)->andReturn($this->createIdP($spEntityId, true, true));
        $isTransparent = $this->mfaHelper->isTransparent($spEntityId, $idpEntityId);
        self::assertTrue($isTransparent);
    }

    /**
     * @test
     * @group EngineBlock
     */
    public function not_transparent_mfa_entity()
    {
        $spEntityId = 'arbitrarySpEntityId';
        $idpEntityId = 'arbitraryIdPEntityId';
        $this->repo->shouldReceive('findIdentityProviderByEntityId')->with($idpEntityId)->andReturn($this->createIdP($spEntityId, true, false));
        $isTransparent = $this->mfaHelper->isTransparent($spEntityId, $idpEntityId);
        self::assertFalse($isTransparent);
    }

    /**
     * @test
     * @group EngineBlock
     */
    public function not_an_mfa_entity()
    {
        $spEntityId = 'arbitrarySpEntityId';
        $idpEntityId = 'arbitraryIdPEntityId';
        $this->repo->shouldReceive('findIdentityProviderByEntityId')->with($idpEntityId)->andReturn($this->createIdP($spEntityId, false, false));
        $isTransparent = $this->mfaHelper->isTransparent($spEntityId, $idpEntityId);
        self::assertFalse($isTransparent);
    }

    /**
     * @test
     * @group EngineBlock
     */
    public function idp_not_found()
    {
        $spEntityId = 'arbitrarySpEntityId';
        $idpEntityId = 'arbitraryIdPEntityId';
        $this->repo->shouldReceive('findIdentityProviderByEntityId')->with($idpEntityId)->andReturn(null);
        $isTransparent = $this->mfaHelper->isTransparent($spEntityId, $idpEntityId);
        self::assertFalse($isTransparent);
    }

    private function createIdP($spEntityId, $isSpInMfaEntities, $isTransparent)
    {
        $idp = m::mock(IdentityProvider::class);
        $mfaEntities = m::mock(MfaEntityCollection::class);
        $mfaEntity = null;
        if ($isSpInMfaEntities) {
            if ($isTransparent) {
                $mfaEntity = m::mock(TransparentMfaEntity::class);
            } else {
                $mfaEntity = m::mock(MfaEntity::class);
            }
        }
        $mfaEntities->shouldReceive('findByEntityId')->with($spEntityId)->andReturn($mfaEntity);
        $idp->shouldReceive('getCoins->mfaEntities')->andReturn($mfaEntities);
        return $idp;
    }
}
