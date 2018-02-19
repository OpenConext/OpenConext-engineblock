<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository;

use Doctrine\DBAL\Query\QueryBuilder;
use Mockery;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\DisableDisallowedEntitiesInWayfVisitor;
use PHPUnit_Framework_TestCase;

/**
 * Class DoctrineMetadataRepositoryTest
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository
 */
class DoctrineMetadataRepositoryTest extends PHPUnit_Framework_TestCase
{
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
