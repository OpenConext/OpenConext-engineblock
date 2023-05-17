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

use Exception;
use Mockery;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Utils;
use PHPUnit_Framework_Error;
use PHPUnit\Framework\TestCase;

/**
 * Class MetadataRepositoryTest
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository
 */
class MetadataRepositoryTest extends TestCase
{
    const MOCK_IDP_NAME = 'https://idp.example.edu';

    public function testFetchIdentityProviderByEntityId()
    {
        $repository = new InMemoryMetadataRepository(array(), array());

        $e = null;
        try {
            $repository->fetchIdentityProviderByEntityId(self::MOCK_IDP_NAME);
        } catch (Exception $e) {
        }
        $this->assertNotNull($e);

        $repository->registerIdentityProvider(new IdentityProvider(self::MOCK_IDP_NAME));
        $this->assertNotNull($repository->fetchIdentityProviderByEntityId(self::MOCK_IDP_NAME));
    }

    public function testFetchServiceProviderByEntityId()
    {
        $repository = new InMemoryMetadataRepository(array(), array());

        $e = null;
        try {
            $repository->fetchServiceProviderByEntityId('https://sp.example.edu');
        } catch (Exception $e) {
        }
        $this->assertNotNull($e);
        $repository->registerServiceProvider(new ServiceProvider('https://sp.example.edu'));
        $this->assertNotNull($repository->fetchServiceProviderByEntityId('https://sp.example.edu'));
    }

    public function testFindServiceProvider()
    {
        $sp = new ServiceProvider('https://entityId');
        $repository = new InMemoryMetadataRepository(array(), array($sp));
        $this->assertEquals($sp, $repository->findServiceProviderByEntityId('https://entityId'));
        $this->assertNull($repository->findServiceProviderByEntityId('https://404.example.edu'));
    }

    public function testFindIdentityProvider()
    {
        $idp = new IdentityProvider('https://entityId');
        $repository = new InMemoryMetadataRepository(array($idp), array());
        $this->assertEquals($idp, $repository->findIdentityProviderByEntityId('https://entityId'));
        $this->assertNull($repository->findIdentityProviderByEntityId('https://404.example.edu'));

        $idps = $repository->findIdentityProviders();
        $this->assertCount(1, $idps);
        $this->assertEquals($idp, $idps[$idp->entityId]);
    }

    public function testFindReservedSchacHomeOrganizations()
    {
        $repository = $this->getFilledRepository();

        $this->assertEquals(array('idp1.example.edu'), $repository->findReservedSchacHomeOrganizations());
    }

    public function testRegisterEntities()
    {
        $repository = new InMemoryMetadataRepository(array(), array());
        $this->assertEmpty($repository->findIdentityProviders());
        $this->assertNull($repository->findServiceProviderByEntityId('https://some.sp.example.edu'));

        $repository->registerIdentityProvider(new IdentityProvider('https://some.idp.example.edu'));
        $this->assertCount(1, $repository->findIdentityProviders());

        $repository->registerServiceProvider(new ServiceProvider('https://some.sp.example.edu'));
        $this->assertNotNull($repository->findServiceProviderByEntityId('https://some.sp.example.edu'));
    }

    public function testFilterApplication()
    {
        $repository = $this->getFilledRepository();

        $mockFilter = Mockery::mock(
            'OpenConext\EngineBlock\Metadata\MetadataRepository\Filter\FilterInterface'
        );
        $mockFilter->shouldReceive('filterRole')->andReturnNull();
        $repository->appendFilter($mockFilter);

        $this->assertEmpty($repository->findIdentityProviders());
        $this->assertEmpty($repository->findAllIdentityProviderEntityIds());
        $this->assertEmpty($repository->findIdentityProvidersByEntityId(array('https://idp2.example.edu')));

        $this->assertNull($repository->findIdentityProviderByEntityId('https://idp1.example.edu'));
        $this->assertNull($repository->findServiceProviderByEntityId('https://sp1.example.edu'));

        $this->assertEmpty($repository->findReservedSchacHomeOrganizations());

        // Make sure the filter is also applied to entity roles added after the filter has been registered.
        $repository->registerIdentityProvider(new IdentityProvider('https://idp4.example.edu'));
        $this->assertNull($repository->findIdentityProviderByEntityId('https://idp4.example.edu'));
    }

    public function testVisitorApplication()
    {
        $repository = $this->getFilledRepository();

        $visitor = Mockery::mock(
            'OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\VisitorInterface'
        );
        $visitor->shouldReceive('visitIdentityProvider')->andReturnUsing(
            function (IdentityProvider $idp) {
                $idp->nameEn = 'MOCKED';
            }
        );

        $repository->appendVisitor($visitor);

        $identityProviders = $repository->findIdentityProviders();
        $this->assertCount(3, $identityProviders);
        foreach ($identityProviders as $identityProvider) {
            $this->assertEquals('MOCKED', $identityProvider->nameEn);
        }
        $identityProviders = $repository->findIdentityProvidersByEntityId(array('https://idp2.example.edu'));
        $this->assertCount(1, $identityProviders);
        $this->assertEquals('MOCKED', reset($identityProviders)->nameEn);
    }

    /**
     * @return InMemoryMetadataRepository
     */
    private function getFilledRepository()
    {
        $repository = new InMemoryMetadataRepository(
            array(
                Utils::instantiate(
                    'OpenConext\EngineBlock\Metadata\Entity\IdentityProvider',
                    array(
                        'entityId' => 'https://idp1.example.edu',
                        'schacHomeOrganization'=> 'idp1.example.edu'
                    )
                ),
                new IdentityProvider('https://idp2.example.edu'),
                new IdentityProvider('https://idp3.example.edu'),
            ),
            array(
                new ServiceProvider('https://sp1.example.edu'),
                new ServiceProvider('https://sp2.example.edu'),
                new ServiceProvider('https://sp3.example.edu'),
            )
        );
        return $repository;
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
