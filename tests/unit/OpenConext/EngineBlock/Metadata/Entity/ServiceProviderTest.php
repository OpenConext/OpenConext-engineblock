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

namespace OpenConext\EngineBlock\Metadata\Entity;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\EmptyMduiElement;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\MduiElement;
use OpenConext\EngineBlock\Metadata\MultilingualValue;
use OpenConext\EngineBlock\Metadata\Organization;
use PHPUnit\Framework\TestCase;

class ServiceProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testInstantiation()
    {
        $entityId = 'https://sp.example.edu';
        $sp = new ServiceProvider($entityId);
        $this->assertEquals($entityId, $sp->entityId);
    }

    /**
     * Expected behavior for display name retrieval is:
     * 1. display name in preferred locale
     * 2. name in preferred locale
     * 3. display name in english
     * 4. name in english
     * 5. entityID (should never happen)
     *
     * @dataProvider displayNameProvider
     */
    public function testGetDisplayName(string $entityId, ?string $displayNameEn, ?string $nameEn, ?string $displayNameLocale, ?string $nameLocale, string $locale, $outcome)
    {
        $sp = $this->createServiceProvider(
            $entityId,
            null,
            null,
            $displayNameEn,
            $displayNameLocale,
            $nameEn,
            $nameLocale
        );

        $this->assertEquals($outcome, $sp->getDisplayName($locale));
    }

    /**
     * Algorithm for organization name is
     * 1. organization display name in preferred locale
     * 2. organization name in preferred locale
     * 3. english organization display name
     * 4. english organization name
     * 5. empty string (will be set to the locale-specific variant of 'unknown' in the template)
     *
     * @dataProvider organizationNameProvider
     */
    public function testGetOrganizationName(string $entityId, ?string $orgDisplayNameEn, ?string $orgNameEn, ?string $orgDisplayNameNl, ?string $orgNameNl, string $locale, $outcome)
    {
        $orgEn = new Organization($orgNameEn, $orgDisplayNameEn, $entityId);
        $orgNl = new Organization($orgNameNl, $orgDisplayNameNl, $entityId);

        $sp = $this->createServiceProvider(
            $entityId,
            $orgEn,
            $orgNl
        );

        $this->assertEquals($outcome, $sp->getOrganizationName($locale));
    }

    /**
     * First item in array = entityId
     * Second item in array = English display name
     * Third item in array = English name
     * Fourth item in array = display name in Dutch locale
     * Fifth item in array = name in Dutch locale
     * Sixth item in array = preferred locale
     * Seventh item in array = normative outcome
     *
     * @return string[][]
     */
    public function displayNameProvider()
    {
        return [
            ['https://entityId.fake', 'JohnnyEnglish', 'English', 'JohnnyEnglish', 'English', 'en', 'JohnnyEnglish'],
            ['https://entityId.fake', null, 'English', null, 'English', 'en', 'English'],
            ['https://entityId.fake', null, null, null, null, 'en', 'https://entityId.fake'],
            ['https://entityId.fake', 'JohnnyEnglish', 'English', 'TijlUilenspiegel', 'Dutch', 'nl', 'TijlUilenspiegel'],
            ['https://entityId.fake', 'JohnnyEnglish', 'English', null, 'Dutch', 'nl', 'Dutch'],
            ['https://entityId.fake', 'JohnnyEnglish', 'English', null, null, 'nl', 'JohnnyEnglish'],
            ['https://entityId.fake', null, 'English', null, null, 'nl', 'English'],
            ['https://entityId.fake', null, null, null, null, 'nl', 'https://entityId.fake'],
        ];
    }

    /**
     * First item in array = entityId
     * Second item in array = English organization display name
     * Third item in array = English organization name
     * Fourth item in array = organization display name in Dutch locale
     * Fifth item in array = organization name in Dutch locale
     * Sixth item in array = preferred locale
     * Seventh item in array = normative outcome
     *
     * @return string[][]
     */
    public function organizationNameProvider()
    {
        return [
            ['https://entityId.fake', 'JohnnyEnglish', 'English', 'JohnnyDutch', 'Dutch', 'en', 'JohnnyEnglish'],
            ['https://entityId.fake', 'JohnnyEnglish', 'English', 'TijlUilenspiegel', 'Dutch', 'nl', 'TijlUilenspiegel'],
            ['https://entityId.fake', null, 'English', 'TijlUilenspiegel', 'Dutch', 'nl', 'TijlUilenspiegel'],
            ['https://entityId.fake', null, 'English', 'TijlUilenspiegel', null, 'nl', 'TijlUilenspiegel'],
            ['https://entityId.fake', null, 'English', null, 'Dutch', 'en', 'English'],
            ['https://entityId.fake', null, 'English', null, 'Dutch', 'nl', 'Dutch'],
            ['https://entityId.fake', 'JohnnyEnglish', 'English', null, 'Dutch', 'nl', 'Dutch'], // follows locale preference even if a preferable org displayname is available
            ['https://entityId.fake', null, null, null, null, 'en', ''],
            ['https://entityId.fake', null, null, 'TijlUilenspiegel', null, 'en', ''], // Even if a preferable dutch translation is available, show the empty English fallback value: ''
            ['https://entityId.fake', 'JohnnyEnglish', 'English', null, null, 'nl', 'JohnnyEnglish'],
            ['https://entityId.fake', null, 'English', null, null, 'nl', 'English'],
            ['https://entityId.fake', null, null, null, null, 'nl', ''],
        ];
    }

    private function createServiceProvider(string $entityId, ?Organization $orgEn = null, ?Organization $orgLocale = null, ?string $displayNameEn = null, ?string $displayNameLocale = null, ?string $nameEn = null, ?string $nameLocale = null)
    {
        $displayNames = [
            new MultilingualValue($displayNameEn, 'en'),
            new MultilingualValue($displayNameLocale, 'nl'),
        ];
        $urls = [
            new MultilingualValue('https://english.example.com/privacy', 'en'),
            new MultilingualValue('https://dutch.example.com/privacy', 'nl'),
            new MultilingualValue('', 'pt'),
        ];

        $displayName = new MduiElement('DisplayName', $displayNames);
        $description = new MduiElement('Description', $displayNames);
        $keywords = new EmptyMduiElement('Keywords');
        $logo = new Logo('https://link-to-my.logo.example.org/img/logo.png');
        $privacyStatementUrl = new MduiElement('PrivacyStatementURL', $urls);

        $mdui = Mdui::fromMetadata(
            $displayName,
            $description,
            $keywords,
            $logo,
            $privacyStatementUrl
        );
        return new ServiceProvider(
            $entityId,
            $mdui,
            $orgEn,
            $orgLocale,
            null,
            null,
            false,
            array(),
            array(),
            '',
            '',
            '',
            false,
            $displayNameEn,
            $displayNameLocale,
            '',
            '',
            '',
            '',
            null,
            $nameEn,
            $nameLocale
        );
    }
}
