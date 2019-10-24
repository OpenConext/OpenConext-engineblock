<?php declare(strict_types=1);
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

namespace OpenConext\EngineBlock\Metadata\Factory\Helper;

use OpenConext\EngineBlock\Metadata\Factory\AbstractEntityTest;
use OpenConext\EngineBlock\Metadata\Factory\Adapter\ServiceProviderEntity;
use OpenConext\EngineBlock\Metadata\IndexedService;
use Mockery;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;

class EngineBlockServiceProviderMetadataTest extends AbstractEntityTest
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function test_abstract_methods()
    {
        $adapter = $this->createServiceProviderAdapter();

        $decorator = new EngineBlockServiceProviderMetadata($adapter);

        $this->runServiceProviderAssertions($adapter, $decorator);
    }

    public function test_helper_methods()
    {
        $organizationEn = Mockery::mock(Organization::class);
        $organizationEn->name = 'metadata-organization-name-en';
        $organizationEn->url = 'metadata-organization-url-en';

        $organizationNl = Mockery::mock(Organization::class);
        $organizationNl->name = 'metadata-organization-name-nl';
        $organizationNl->url = 'metadata-organization-url-nl';

        $cert1 = Mockery::mock(X509Certificate::class);
        $cert1->shouldReceive('toCertData')->andReturn('pem1-abc');
        $cert2 = Mockery::mock( X509Certificate::class);
        $cert2->shouldReceive('toCertData')->andReturn('pem2-abc');

        $adapter = Mockery::mock(ServiceProviderEntity::class);

        $adapter->shouldReceive('getOrganizationEn')->andReturn($organizationEn);
        $adapter->shouldReceive('getOrganizationNl')->andReturn($organizationNl);
        $adapter->shouldReceive('getAssertionConsumerServices')->andReturn([
            new IndexedService('location1','binding1', 0),
            new IndexedService('location2','binding2', 1),
        ]);
        $adapter->shouldReceive('getDisplayNameEn')->andReturn('metadata-display-name-en');
        $adapter->shouldReceive('getDisplayNameNl')->andReturn('metadata-display-name-nl');
        $adapter->shouldReceive('getCertificates')->andReturn([
            $cert1, $cert2,
        ]);

        $decorator = new EngineBlockServiceProviderMetadata($adapter);

        $this->assertEquals('metadata-organization-name-en', $decorator->getOrganization());
        $this->assertEquals('metadata-organization-url-en', $decorator->getOrganizationSupportUrl());
        $this->assertEquals( new IndexedService('location1','binding1', 0), $decorator->getAssertionConsumerService());
        $this->assertEquals(true, $decorator->hasUiInfo());
        $this->assertEquals(true, $decorator->hasOrganizationInfo());
        $this->assertEquals(['pem1-abc' => 'pem1-abc', 'pem2-abc' => 'pem2-abc'], $decorator->getPublicKeys());
    }
}
