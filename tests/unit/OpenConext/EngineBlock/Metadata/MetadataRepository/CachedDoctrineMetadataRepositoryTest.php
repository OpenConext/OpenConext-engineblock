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
use OpenConext\EngineBlock\Metadata\MetadataRepository\Filter\FilterInterface;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\VisitorInterface;
use PHPUnit\Framework\TestCase;

class CachedDoctrineMetadataRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testMethodsCallsAreProxied()
    {
        $doctrineRepository = Mockery::mock('OpenConext\EngineBlock\Metadata\MetadataRepository\DoctrineMetadataRepository');
        $doctrineRepository->shouldReceive('findIdentityProviderByEntityId');
        $doctrineRepository->shouldReceive('findServiceProviderByEntityId');
        $doctrineRepository->shouldReceive('findIdentityProviderByEntityId');
        $doctrineRepository->shouldReceive('findIdentityProviders');
        $doctrineRepository->shouldReceive('findIdentityProvidersByEntityId');
        $doctrineRepository->shouldReceive('findAllIdentityProviderEntityIds');
        $doctrineRepository->shouldReceive('findReservedSchacHomeOrganizations');

        $repository = new CachedDoctrineMetadataRepository($doctrineRepository);
        $repository->findIdentityProviderByEntityId('test');
        $repository->findServiceProviderByEntityId('test');
        $repository->findIdentityProviderByEntityId('test');
        $repository->findIdentityProviders();
        $repository->findIdentityProvidersByEntityId(['test']);
        $repository->findAllIdentityProviderEntityIds();
        $repository->findReservedSchacHomeOrganizations();
    }

    public function testFetchIdentityProviderThrowExceptions()
    {
        $doctrineRepository = Mockery::mock('OpenConext\EngineBlock\Metadata\MetadataRepository\DoctrineMetadataRepository');
        $doctrineRepository->shouldReceive('findIdentityProviderByEntityId');

        $this->expectException(EntityNotFoundException::class);

        $repository = new CachedDoctrineMetadataRepository($doctrineRepository);
        $repository->fetchIdentityProviderByEntityId('test');
    }

    public function testFetchServiceProviderThrowExceptions()
    {
        $doctrineRepository = Mockery::mock('OpenConext\EngineBlock\Metadata\MetadataRepository\DoctrineMetadataRepository');
        $doctrineRepository->shouldReceive('findServiceProviderByEntityId');

        $this->expectException(EntityNotFoundException::class);

        $repository = new CachedDoctrineMetadataRepository($doctrineRepository);
        $repository->fetchServiceProviderByEntityId('test');
    }

    public function testAppendVisitor()
    {
        $doctrineRepository = Mockery::mock('OpenConext\EngineBlock\Metadata\MetadataRepository\DoctrineMetadataRepository');
        $doctrineRepository->shouldReceive('appendVisitor');

        $repository = new CachedDoctrineMetadataRepository($doctrineRepository);
        $repository->appendVisitor(
            Mockery::mock(VisitorInterface::class)
        );
    }

    public function testAppendFilter()
    {
        $doctrineRepository = Mockery::mock('OpenConext\EngineBlock\Metadata\MetadataRepository\DoctrineMetadataRepository');
        $doctrineRepository->shouldReceive('appendFilter');

        $repository = new CachedDoctrineMetadataRepository($doctrineRepository);
        $repository->appendFilter(
            Mockery::mock(FilterInterface::class)
        );
    }
}
