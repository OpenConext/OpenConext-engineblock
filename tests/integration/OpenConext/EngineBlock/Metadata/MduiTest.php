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

namespace OpenConext\EngineBlock\Tests;

use OpenConext\EngineBlock\Exception\MduiNotFoundException;
use OpenConext\EngineBlock\Metadata\EmptyMduiElement;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\MduiElement;
use OpenConext\EngineBlock\Metadata\MultilingualElement;
use OpenConext\EngineBlock\Metadata\MultilingualValue;
use PHPUnit\Framework\TestCase;

class MduiTest extends TestCase
{
    private $displayName;

    private $description;

    private $keywords;

    private $emptyMduiElement;

    private $logo;

    private $privacyStatementUrl;

    private $discoHints;

    protected function setUp(): void
    {
        $displayNames = [
            new MultilingualValue('bogus en value', 'en'),
            new MultilingualValue('bogus nl value', 'nl'),
        ];
        $urls = [
            new MultilingualValue('https://english.example.com/privacy', 'en'),
            new MultilingualValue('https://dutch.example.com/privacy', 'nl'),
            new MultilingualValue('', 'pt'),
        ];

        $this->displayName = new MduiElement('DisplayName', $displayNames);
        $this->description = new MduiElement('Description', $displayNames);
        $this->keywords = new MduiElement('Keywords', $displayNames);
        $this->emptyMduiElement = new EmptyMduiElement('Keywords');
        $this->logo = new Logo('https://link-to-my.logo.example.org/img/logo.png');
        $this->privacyStatementUrl = new MduiElement('PrivacyStatementURL', $urls);
        $this->discoHints = new MduiElement('DiscoHints', $displayNames);
    }

    public function test_valid_mdui()
    {
        $mdui = Mdui::fromMetadata(
            $this->displayName,
            $this->description,
            $this->keywords,
            $this->logo,
            $this->privacyStatementUrl
        );
        $json = $mdui->toJson();
        $this->assertEquals(
            '{"DisplayName":{"name":"DisplayName","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Description":{"name":"Description","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Keywords":{"name":"Keywords","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Logo":{"name":"Logo","url":"https:\/\/link-to-my.logo.example.org\/img\/logo.png","width":null,"height":null},"PrivacyStatementURL":{"name":"PrivacyStatementURL","values":{"en":{"value":"https:\/\/english.example.com\/privacy","language":"en"},"nl":{"value":"https:\/\/dutch.example.com\/privacy","language":"nl"},"pt":{"value":"","language":"pt"}}}}',
            $json
        );
    }

    public function test_create_mdui_from_json()
    {
        $creationJson = '{"DisplayName":{"name":"DisplayName","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Description":{"name":"Description","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Keywords":{"name":"Keywords","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Logo":{"name":"Logo","url":"https:\/\/link-to-my.logo.example.org\/img\/logo.png","width":null,"height":null},"PrivacyStatementURL":{"name":"PrivacyStatementURL","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}}}';
        // Create a value object from json
        $mdui = Mdui::fromJson($creationJson);
        // Cast it back to json
        $generatedJson = $mdui->toJson();
        // The JSON blobs should be equal
        $this->assertEquals(
            $creationJson,
            $generatedJson
        );
    }

    /**
     * @dataProvider invalidJsonProvider
     */
    public function test_creates_empty_mdui_from_json_invalid_json($invalidJson)
    {
        $mdui = Mdui::fromJson($invalidJson);
        // english translation for display name should be present, but display name is misconfigured
        $this->assertNull($mdui->getDisplayNameOrNull('en'));
    }

    public function invalidJsonProvider()
    {
        return [
           'single quotes' => ['{"DisplayName":{\'name\':\'DisplayName\',"values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Description":{"name":"Description","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Keywords":{"name":"Keywords","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Logo":{"name":"Logo","url":"https:\/\/link-to-my.logo.example.org\/img\/logo.png","width":null,"height":null},"PrivacyStatementURL":{"name":"PrivacyStatementURL","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}}}'],
           'misspelled values' => ['{"DisplayName":{"name":"DisplayName","vallues":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Description":{"name":"Description","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Keywords":{"name":"Keywords","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Logo":{"name":"Logo","url":"https:\/\/link-to-my.logo.example.org\/img\/logo.png","width":null,"height":null},"PrivacyStatementURL":{"name":"PrivacyStatementURL","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}}}'],
           'misspelled multilingual entry' => ['{"DisplayName":{"name":"DisplayName","values":{"en":{"vaue":"bogus en value","lang":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Description":{"name":"Description","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Keywords":{"name":"Keywords","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Logo":{"name":"Logo","url":"https:\/\/link-to-my.logo.example.org\/img\/logo.png","width":null,"height":null},"PrivacyStatementURL":{"name":"PrivacyStatementURL","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}}}'],
           'misspelled DispleyName' => ['{"DispleyName":{"name":"DisplayName","values":{"en":{"vaue":"bogus en value","lang":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Description":{"name":"Description","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Keywords":{"name":"Keywords","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Logo":{"name":"Logo","url":"https:\/\/link-to-my.logo.example.org\/img\/logo.png","width":null,"height":null},"PrivacyStatementURL":{"name":"PrivacyStatementURL","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}}}'],
        ];
    }

    public function test_mdui_data_can_be_json_serialized()
    {
        $mdui = Mdui::fromMetadata(
            $this->displayName,
            $this->description,
            $this->keywords,
            $this->logo,
            $this->privacyStatementUrl
        );
        $json = $mdui->toJson();
        $this->assertJson($json);
    }

    public function test_empty_elements_are_permitted()
    {
        $mdui = Mdui::fromMetadata(
            $this->displayName,
            $this->description,
            $this->emptyMduiElement,
            $this->logo,
            $this->privacyStatementUrl
        );
        $json = $mdui->toJson();
        $this->assertEquals(
            '{"DisplayName":{"name":"DisplayName","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Description":{"name":"Description","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Keywords":{"name":"Keywords"},"Logo":{"name":"Logo","url":"https:\/\/link-to-my.logo.example.org\/img\/logo.png","width":null,"height":null},"PrivacyStatementURL":{"name":"PrivacyStatementURL","values":{"en":{"value":"https:\/\/english.example.com\/privacy","language":"en"},"nl":{"value":"https:\/\/dutch.example.com\/privacy","language":"nl"},"pt":{"value":"","language":"pt"}}}}',
            $json
        );
    }

    public function test_create_mdui_from_json_with_empty_keywords()
    {
        $creationJson = '{"DisplayName":{"name":"DisplayName","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Description":{"name":"Description","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}},"Keywords":{"name":"Keywords"},"Logo":{"name":"Logo","url":"https:\/\/link-to-my.logo.example.org\/img\/logo.png","width":null,"height":null},"PrivacyStatementURL":{"name":"PrivacyStatementURL","values":{"en":{"value":"bogus en value","language":"en"},"nl":{"value":"bogus nl value","language":"nl"}}}}';
        // Create a value object from json
        $mdui = Mdui::fromJson($creationJson);
        // Cast it back to json
        $generatedJson = $mdui->toJson();
        // The JSON blobs should be equal
        $this->assertEquals(
            $creationJson,
            $generatedJson
        );
    }
    public function test_elements_must_be_permitted()
    {
        $this->expectException(MduiNotFoundException::class);
        $this->expectExceptionMessage('The Mdui element identified by: DiscoHints is not supported by EngineBlock. The following are permitted: DisplayName, Description, Keywords, Logo, PrivacyStatementURL');
        Mdui::fromMetadata(
            $this->displayName,
            $this->discoHints,
            $this->keywords,
            $this->logo,
            $this->privacyStatementUrl
        );
    }

    public function test_has_privacy_statement_url()
    {
        $mdui = Mdui::fromMetadata(
            $this->displayName,
            $this->description,
            $this->keywords,
            $this->logo,
            $this->privacyStatementUrl
        );
        $this->assertTrue($mdui->hasPrivacyStatementURL('en'));
        $this->assertTrue($mdui->hasPrivacyStatementURL('nl'));
        $this->assertTrue($mdui->hasPrivacyStatementURL('pt')); // would yield the english privacy statement

        $mdui = Mdui::fromMetadata(
            $this->displayName,
            $this->description,
            $this->keywords,
            $this->logo,
            $this->emptyMduiElement
        );
        $this->assertFalse($mdui->hasPrivacyStatementURL('en'));
        $this->assertFalse($mdui->hasPrivacyStatementURL('nl'));
        $this->assertFalse($mdui->hasPrivacyStatementURL('pt'));
    }

    public function test_privacy_statement_url_fallback()
    {
        $mdui = Mdui::fromMetadata(
            $this->displayName,
            $this->description,
            $this->keywords,
            $this->logo,
            $this->privacyStatementUrl
        );

        // requesting a preferred existing language gives that language
        $this->assertEquals('https://dutch.example.com/privacy', $mdui->getPrivacyStatementURL('nl'));
        $this->assertEquals('https://english.example.com/privacy', $mdui->getPrivacyStatementURL('en'));
        // requesting a non existant language falls back on the primary language (en)
        $this->assertEquals('https://english.example.com/privacy', $mdui->getPrivacyStatementURL('pt'));
    }

    public function test_privacy_statement_url_supports_only_known_languages()
    {
        $mdui = Mdui::fromMetadata(
            $this->displayName,
            $this->description,
            $this->keywords,
            $this->logo,
            $this->privacyStatementUrl
        );

        // When the language is not known, an exception is raised
        $this->expectException(MduiNotFoundException::class);
        $this->expectExceptionMessage("Unable to find the element value named: PrivacyStatementURL for language 'de'");
        $mdui->getPrivacyStatementURL('de');
    }

    public function test_has_privacy_statement_url_supports_only_known_languages()
    {
        $mdui = Mdui::fromMetadata(
            $this->displayName,
            $this->description,
            $this->keywords,
            $this->logo,
            $this->privacyStatementUrl
        );

        // When the language is not known, an exception is raised
        $this->expectException(MduiNotFoundException::class);
        $this->expectExceptionMessage("Unable to find the element value named: PrivacyStatementURL for language 'sp'");
        $mdui->hasPrivacyStatementURL('sp');
    }

    public function test_getting_the_configured_languages()
    {
        $mdui = Mdui::fromMetadata(
            $this->displayName,
            $this->description,
            $this->emptyMduiElement, // Keywords are left empty intentional
            $this->logo, // Logo only carries a 'translation' for the primary language
            $this->privacyStatementUrl // Also carries a third languages
        );

        $this->assertEquals(['en', 'nl'], $mdui->getLanguagesByElementName('DisplayName'));
        $this->assertEquals(['en', 'nl'], $mdui->getLanguagesByElementName('Description'));
        $this->assertEmpty($mdui->getLanguagesByElementName('Keywords'));
        $this->assertEquals([MultilingualElement::PRIMARY_LANGUAGE], $mdui->getLanguagesByElementName('Logo'));
        $this->assertEquals(['en', 'nl', 'pt'], $mdui->getLanguagesByElementName('PrivacyStatementURL'));
    }
}
