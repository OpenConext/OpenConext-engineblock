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

use Mockery;
use OpenConext\EngineBlock\Metadata\Factory\AbstractEntityTest;
use OpenConext\EngineBlock\Metadata\Factory\Adapter\IdentityProviderEntity;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use OpenConext\EngineBlockBundle\Localization\LanguageSupportProvider;

class EngineBlockIdentityProviderMetadataTest extends AbstractEntityTest
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
    /**
     * @var LanguageSupportProvider
     */
    private $languageProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $languages = ['en','nl','pt'];
        $this->languageProvider = new LanguageSupportProvider($languages, $languages);
    }

    public function test_abstract_methods()
    {
        $adapter = $this->createIdentityProviderAdapter();

        $decorator = new IdentityProviderMetadataHelper($adapter, $this->languageProvider);

        $this->runIdentityProviderAssertions($adapter, $decorator);
    }

    public function test_hepler_methods()
    {
        $organizationEn = Mockery::mock(Organization::class);
        $organizationEn->name = 'metadata-organization-name-en';
        $organizationEn->url = 'metadata-organization-url-en';

        $organizationNl = Mockery::mock(Organization::class);
        $organizationNl->name = 'metadata-organization-name-nl';
        $organizationNl->url = 'metadata-organization-url-nl';

        $organizationPt = Mockery::mock(Organization::class);
        $organizationPt->name = 'metadata-organization-name-pt';
        $organizationPt->url = 'metadata-organization-url-pt';

        $cert1 = Mockery::mock(X509Certificate::class);
        $cert1->shouldReceive('toCertData')->andReturn('pem1-abc');
        $cert2 = Mockery::mock(X509Certificate::class);
        $cert2->shouldReceive('toCertData')->andReturn('pem2-abc');

        $adapter = Mockery::mock(IdentityProviderEntity::class);

        $adapter->shouldReceive('getOrganization')->with('en')->andReturn($organizationEn);
        $adapter->shouldReceive('getOrganization')->with('nl')->andReturn($organizationNl);
        $adapter->shouldReceive('getOrganization')->with('pt')->andReturn($organizationPt);
        $adapter->shouldReceive('getSingleSignOnServices')->andReturn([
            new Service('location1', 'binding1'),
            new Service('location2', 'binding2'),
        ]);

        $adapter->shouldReceive('getCertificates')->andReturn([
            $cert1, $cert2,
        ]);

        $decorator = new IdentityProviderMetadataHelper($adapter, $this->languageProvider);

        $this->assertEquals('metadata-organization-name-en', $decorator->getOrganizationName('en'));
        $this->assertEquals('metadata-organization-name-nl', $decorator->getOrganizationName('nl'));
        $this->assertEquals('metadata-organization-name-pt', $decorator->getOrganizationName('pt'));
        $this->assertEquals('metadata-organization-url-en', $decorator->getOrganizationUrl('en'));
        $this->assertEquals('metadata-organization-url-nl', $decorator->getOrganizationUrl('nl'));
        $this->assertEquals('metadata-organization-url-pt', $decorator->getOrganizationUrl('pt'));
        $this->assertEquals('location1', $decorator->getSsoLocation());
        $this->assertEquals(true, $decorator->hasOrganizationInfo());
        $this->assertEquals(['pem1-abc' => 'pem1-abc', 'pem2-abc' => 'pem2-abc'], $decorator->getPublicKeys());
    }
}
