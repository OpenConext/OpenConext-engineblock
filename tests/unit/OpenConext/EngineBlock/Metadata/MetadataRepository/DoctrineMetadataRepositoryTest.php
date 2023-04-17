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

namespace OpenConext\EngineBlock\Metadata\MetadataRepository;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\DisableDisallowedEntitiesInWayfVisitor;
use PHPUnit\Framework\TestCase;

class DoctrineMetadataRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testFindIdentityProviders()
    {
        $mockQueryBuilder = Mockery::mock('Doctrine\ORM\QueryBuilder');
        $mockQueryBuilder
            ->shouldReceive('getQuery->execute')
            ->andReturn([new IdentityProvider('https://idp.entity.com')]);

        $mockSpRepository = Mockery::mock('Doctrine\ORM\EntityRepository');
        $mockIdpRepository = Mockery::mock('Doctrine\ORM\EntityRepository');
        $mockIdpRepository
            ->shouldReceive('getClassName')
            ->andReturn('OpenConext\EngineBlock\Metadata\Entity\IdentityProvider')
            ->shouldReceive('createQueryBuilder')
            ->andReturn($mockQueryBuilder);

        $repository = new DoctrineMetadataRepository(
            Mockery::mock('Doctrine\ORM\EntityManager'),
            $mockSpRepository,
            $mockIdpRepository
        );

        $this->assertCount(1, $repository->findIdentityProviders());
    }

    public function testFindIdentityProvidersVisitor()
    {
        $mockQueryBuilder = Mockery::mock('Doctrine\ORM\QueryBuilder');
        $mockQueryBuilder
            ->shouldReceive('getQuery->execute')
            ->andReturn([
                new IdentityProvider('https://idp.entity.com'),
                new IdentityProvider('https://unconnected.entity.com')
            ]);

        $mockSpRepository = Mockery::mock('Doctrine\ORM\EntityRepository');
        $mockIdpRepository = Mockery::mock('Doctrine\ORM\EntityRepository');
        $mockIdpRepository
            ->shouldReceive('getClassName')
            ->andReturn('OpenConext\EngineBlock\Metadata\Entity\IdentityProvider')
            ->shouldReceive('createQueryBuilder')
            ->andReturn($mockQueryBuilder);

        $repository = new DoctrineMetadataRepository(
            Mockery::mock('Doctrine\ORM\EntityManager'),
            $mockSpRepository,
            $mockIdpRepository
        );

        $repository->appendVisitor(new DisableDisallowedEntitiesInWayfVisitor(['https://idp.entity.com']));
        $identityProviders = $repository->findIdentityProviders();

        $expectedWayfVisibility = [
            'https://idp.entity.com' => true,
            'https://unconnected.entity.com' => false,
        ];

        foreach ($identityProviders as $identityProvider){
            $this->assertEquals($expectedWayfVisibility[$identityProvider->entityId], $identityProvider->enabledInWayf);
        }

        $this->assertCount(2, $identityProviders);
    }
}
