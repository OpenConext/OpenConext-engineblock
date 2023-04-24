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

namespace OpenConext\EngineBlock\Metadata\Factory;

use OpenConext\EngineBlock\Metadata\EmptyMduiElement;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\MduiElement;
use PHPUnit\Framework\TestCase;
use stdClass;
use function is_string;

class MduiPushAssemblerFactoryTest extends TestCase
{
    public function test_assemble_mdui_empty_parameters()
    {
        $mdui = MduiPushAssemblerFactory::buildFrom([], new stdClass());
        $this->assertInstanceOf(Mdui::class, $mdui);
        $this->assertFalse($mdui->hasDisplayName('en'));
        $this->assertFalse($mdui->hasDisplayName('nl'));
        $this->assertFalse($mdui->hasDisplayName('pt'));
        $this->assertEmpty($mdui->getDescription('en'));
        $this->assertEmpty($mdui->getDescription('nl'));
        $this->assertEmpty($mdui->getDescription('pt'));
        $this->assertFalse($mdui->hasKeywords('en'));
        $this->assertFalse($mdui->hasKeywords('nl'));
        $this->assertFalse($mdui->hasKeywords('pt'));
        $this->assertInstanceOf(EmptyMduiElement::class, $mdui->getLogo());
        $this->assertFalse($mdui->hasPrivacyStatementURL('en'));
    }

    public function test_assemble_mdui_sensible_parameters()
    {
        $parameters = [
            "displayNameNl" => null,
            "displayNameEn" => "English display name is set",
            "displayNamePt" => null,
            "descriptionNl" => null,
            "descriptionEn" => "Description is set",
            "descriptionPt" => null,
            "keywordsEn" => "Keywords, EN, are set",
            "keywordsNl" => "Keywords, NL, are set",
            "keywordsPt" => null
        ];
        $mdui = MduiPushAssemblerFactory::buildFrom($parameters, new stdClass());
        $this->assertInstanceOf(Mdui::class, $mdui);
        $this->assertEquals('English display name is set', $mdui->getDisplayName('en'));
        $this->assertEquals('Description is set', $mdui->getDescription('en'));
        $this->assertTrue($mdui->hasKeywords('en'));
        $this->assertTrue($mdui->hasKeywords('nl'));
        $this->assertEquals('Keywords, EN, are set', $mdui->getKeywords('en'));
        $this->assertEquals('Keywords, NL, are set', $mdui->getKeywords('nl'));

        $this->assertInstanceOf(EmptyMduiElement::class, $mdui->getLogo());
        $this->assertFalse($mdui->hasPrivacyStatementURL('en'));
    }

    public function test_assemble_mdui_sensible_parameters_including_logo()
    {
        $parameters = [
            "displayNameNl" => null,
            "displayNameEn" => "English display name is set",
            "displayNamePt" => null,
            "descriptionNl" => null,
            "descriptionEn" => "Description is set",
            "descriptionPt" => null,
            "keywordsEn" => null,
            "keywordsNl" => null,
            "keywordsPt" => null,
            "logo" => new Logo('https://foobar.example.com/logo.png')
        ];
        $mdui = MduiPushAssemblerFactory::buildFrom($parameters, new stdClass());
        $this->assertEquals('https://foobar.example.com/logo.png', $mdui->getLogo()->url);
    }

    public function test_assemble_mdui_sensible_parameters_including_privacy_statment_url()
    {
        $parameters = [
            "displayNameNl" => null,
            "displayNameEn" => "English display name is set",
            "displayNamePt" => null,
            "descriptionNl" => null,
            "descriptionEn" => "Description is set",
            "descriptionPt" => null,
            "keywordsEn" => "Keywords, EN, are set",
            "keywordsNl" => "Keywords, NL, are set",
            "keywordsPt" => null,
            "logo" => new Logo('https://foobar.example.com/logo.png')
        ];

        $connection = new stdClass();
        $connection->metadata = new stdClass();
        $connection->metadata->PrivacyStatementURL = new stdClass();
        $connection->metadata->PrivacyStatementURL->en = 'https://english-privacy.statment.url.com';
        $connection->metadata->PrivacyStatementURL->nl = 'https://dutch-privacy.statment.url.nl';

        $mdui = MduiPushAssemblerFactory::buildFrom($parameters, $connection);
        $this->assertTrue($mdui->hasPrivacyStatementURL('en'));
        $this->assertEquals('https://english-privacy.statment.url.com', $mdui->getPrivacyStatementURL('en'));
        $this->assertEquals('https://dutch-privacy.statment.url.nl', $mdui->getPrivacyStatementURL('nl'));
    }
}
