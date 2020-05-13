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

namespace OpenConext\EngineBlock\Metadata\Factory\Decorator;

use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Factory\AbstractEntityTest;
use OpenConext\EngineBlock\Metadata\Factory\ValueObject\EngineBlockConfiguration;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Organization;
use Symfony\Component\Translation\TranslatorInterface;

class EngineblockServiceProviderInformationTest extends AbstractEntityTest
{

    public function test_methods()
    {
        $adapter = $this->createServiceProviderAdapter();

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->at(0))
            ->method('trans')
            ->with('suite_name')
            ->willReturn('test-suite');

        $translator->expects($this->at(1))
            ->method('trans')
            ->with('openconext_support_url')
            ->willReturn('configuredSupportUrl');

        $configuration = new EngineBlockConfiguration(
            $translator,
            'configuredSupportMail',
            'configuredDescription',
            'example.org',
            '/configuredLogoUrl.gif',
            1209,
            1009
        );

        $decorator = new EngineBlockServiceProviderInformation($adapter, $configuration);

        // Logo we would expect
        $logo = new Logo('https://example.org/configuredLogoUrl.gif');
        $logo->width = 1209;
        $logo->height = 1009;

        // Organization we would expect
        $organization = new Organization('test-suite', 'test-suite', 'configuredSupportUrl');

        // contacts we would expect
        $contactPersons = [
            ContactPerson::from('support', 'test-suite', 'Support', 'configuredSupportMail'),
            ContactPerson::from('technical', 'test-suite', 'Support', 'configuredSupportMail'),
            ContactPerson::from('administrative', 'test-suite', 'Support', 'configuredSupportMail'),
        ];

        // the actual assertions
        $overrides = [];
        $overrides['nameNl'] = 'test-suite EngineBlock';
        $overrides['nameEn'] = 'test-suite EngineBlock';
        $overrides['namePt'] = 'test-suite EngineBlock';
        $overrides['displayNameNl'] = 'test-suite EngineBlock';
        $overrides['displayNameEn'] = 'test-suite EngineBlock';
        $overrides['displayNamePt'] = 'test-suite EngineBlock';
        $overrides['descriptionNl'] = 'configuredDescription';
        $overrides['descriptionEn'] = 'configuredDescription';
        $overrides['descriptionPt'] = 'configuredDescription';
        $overrides['logo'] = $logo;
        $overrides['organizationNl'] = $organization;
        $overrides['organizationEn'] = $organization;
        $overrides['organizationPt'] = $organization;
        $overrides['contactPersons'] = $contactPersons;

        $this->runServiceProviderAssertions($adapter, $decorator, $overrides);
    }
}
