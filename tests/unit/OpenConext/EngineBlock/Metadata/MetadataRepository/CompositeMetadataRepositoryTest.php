<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository;

use Mockery;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Utils;
use PHPUnit_Framework_TestCase;

/**
 * Class CompositeMetadataRepositoryTest
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository
 */
class CompositeMetadataRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function testOrdering()
    {
        $repository = $this->createFilledRepository();

        // Get the Identity Provider from the first metadata repository that has it listed without 'publishInEdugain'.
        $idp1 = $repository->findIdentityProviderByEntityId('https://idp1.example.edu');
        $this->assertFalse($idp1->publishInEdugain);

        // Only available in the second repository WITH 'publishInEdugain'.
        $idp2 = $repository->findIdentityProviderByEntityId('https://idp2.example.edu');
        $this->assertTrue($idp2->publishInEdugain);
    }

    public function testVisitors()
    {
        $mockVisitor1 = Mockery::mock(
            'OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\VisitorInterface'
        );
        $mockVisitor1->shouldReceive('visitIdentityProvider')->andReturnUsing(function (IdentityProvider $idp) {
            $idp->nameEn = "VISITOR1";
            $idp->descriptionEn = "VISITOR1";
        });
        $mockVisitor2 = Mockery::mock(
            'OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\VisitorInterface'
        );
        $mockVisitor2->shouldReceive('visitIdentityProvider')->andReturnUsing(function (IdentityProvider $idp) {
            $idp->nameEn = 'VISITOR2';
        });

        $repository = $this->createFilledRepository();

        $repository->appendVisitor($mockVisitor1);
        $repository->appendVisitor($mockVisitor2);

        $idp = $repository->fetchIdentityProviderByEntityId('https://idp1.example.edu');
        $this->assertEquals('VISITOR2', $idp->nameEn);
        $this->assertEquals('VISITOR1', $idp->descriptionEn);
    }

    public function testFindIdentityProviders()
    {
        $repository = $this->createFilledRepository();

        $idps = $repository->findIdentityProviders();
        $this->assertCount(2, $idps);

        /** @var IdentityProvider $idp1 */
        $idp1 = reset($idps);
        $idp1EntityId = key($idps);
        $this->assertEquals($idp1EntityId, 'https://idp1.example.edu');
        $this->assertEquals($idp1->entityId, $idp1EntityId);
        $this->assertEquals('https://idp1.example.edu', $idp1->entityId);

        /** @var IdentityProvider $idp2 */
        $idp2 = next($idps);
        $idp2EntityId = key($idps);
        $this->assertEquals($idp2EntityId, 'https://idp2.example.edu');
        $this->assertEquals($idp2->entityId, $idp2EntityId);
        $this->assertTrue($idp2->publishInEdugain);
        $this->assertEquals('https://idp2.example.edu', $idp2->entityId);

        $this->assertEquals(
            array('https://idp1.example.edu', 'https://idp2.example.edu'),
            $repository->findAllIdentityProviderEntityIds()
        );
    }

    public function testFindReservedSchacHomeOrganizations()
    {
        $repository = $this->createFilledRepository();

        $this->assertEquals(
            array('idp1.example.edu', 'idp2.example.edu'),
            $repository->findReservedSchacHomeOrganizations()
        );
    }

    public function testFindAllowedIdpEntityIds()
    {
        $repository = $this->createFilledRepository();

        $sp = new ServiceProvider('https://sp1.example.edu');
        $sp->allowedIdpEntityIds = [
            'https://idp1.example.edu',
            'https://idp2.example.edu',
        ];

        $this->assertEquals(
            $repository->findAllIdentityProviderEntityIds(),
            $repository->findAllowedIdpEntityIdsForSp($sp)
        );
    }

    public function testFindEntitiesPublishableInEdugain()
    {
        $repository = $this->createFilledRepository();

        $publishableEntities = $repository->findEntitiesPublishableInEdugain();
        $this->assertCount(4, $publishableEntities);
        /** @var AbstractRole $entity1 */
        $entity1 = reset($publishableEntities);
        $this->assertEquals('https://idp1.example.edu', $entity1->entityId);
        $this->assertEquals('2', $entity1->displayNameEn);

        /** @var AbstractRole $entity2 */
        $entity2 = next($publishableEntities);
        $this->assertEquals('https://idp2.example.edu', $entity2->entityId);

        /** @var AbstractRole $entity3 */
        $entity3 = next($publishableEntities);
        $this->assertEquals('https://sp1.example.edu', $entity3->entityId);

        /** @var AbstractRole $entity4 */
        $entity4 = next($publishableEntities);
        $this->assertEquals('https://sp2.example.edu', $entity4->entityId);
    }

    /**
     * @return CompositeMetadataRepository
     */
    private function createFilledRepository()
    {
        $repository = new CompositeMetadataRepository(
            array(
                new InMemoryMetadataRepository(
                    array(
                        Utils::instantiate(
                            'OpenConext\EngineBlock\Metadata\Entity\IdentityProvider',
                            array(
                                'entityId' => 'https://idp1.example.edu',
                                'schacHomeOrganization' => 'idp1.example.edu',
                                'displayNameEn' => '1',
                            )
                        ),
                    ),
                    array(
                        new ServiceProvider('https://sp1.example.edu')
                    )
                ),
            )
        );
        $repository->appendRepository(new InMemoryMetadataRepository(
            array(
                Utils::instantiate(
                    'OpenConext\EngineBlock\Metadata\Entity\IdentityProvider',
                    array(
                        'entityId' => 'https://idp1.example.edu',
                        'publishInEdugain' => true,
                        'schacHomeOrganization' => 'idp1.example.edu',
                        'displayNameEn' => '2',
                    )
                ),
                Utils::instantiate(
                    'OpenConext\EngineBlock\Metadata\Entity\IdentityProvider',
                    array(
                        'entityId' => 'https://idp2.example.edu',
                        'publishInEdugain' => true,
                        'schacHomeOrganization' => 'idp2.example.edu',
                    )
                )
            ),
            array(
                Utils::instantiate(
                    'OpenConext\EngineBlock\Metadata\Entity\ServiceProvider',
                    array('entityId' => 'https://sp1.example.edu', 'publishInEdugain' => true)
                ),
                Utils::instantiate(
                    'OpenConext\EngineBlock\Metadata\Entity\ServiceProvider',
                    array('entityId' => 'https://sp2.example.edu', 'publishInEdugain' => true)
                )
            )
        ));
        return $repository;
    }
}
