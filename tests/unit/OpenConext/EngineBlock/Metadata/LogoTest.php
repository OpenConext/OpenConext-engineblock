<?php

/**
 * Copyright 2023 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Metadata;

use JsonSerializable;
use OpenConext\EngineBlock\Exception\MduiRuntimeException;
use PHPUnit\Framework\TestCase;

class LogoTest extends TestCase
{
    /**
     * @param string $json
     * @dataProvider provideCorrectJson
     */
    public function test_create_logo_from_json_happy_flow(string $json): void
    {
        $jsonData = json_decode($json, true);
        $logo = Logo::fromJson($jsonData);
        self::assertInstanceOf(Logo::class, $logo);
        self::assertInstanceOf(MultilingualElement::class, $logo);
        self::assertInstanceOf(JsonSerializable::class, $logo);
        self::assertEquals($jsonData['url'], $logo->url);

        if (array_key_exists('width', $jsonData)) {
            self::assertEquals($jsonData['width'], $logo->width);
        }
        if (array_key_exists('height', $jsonData)) {
            self::assertEquals($jsonData['height'], $logo->height);
        }
        self::assertIsArray($logo->jsonSerialize());
    }

    /**
     * @param string $json
     * @dataProvider provideUrlMissingJson
     */
    public function test_create_logo_from_json_requires_url(string $json): void
    {
        self::expectException(MduiRuntimeException::class);
        self::expectExceptionMessage(
            'Incomplete MDUI Logo data. The URL is missing while serializing the data from JSON'
        );
        Logo::fromJson(json_decode($json, true));
    }

    public function test_logo_can_not_be_translated()
    {
        self::expectException(MduiRuntimeException::class);
        self::expectExceptionMessage('We do not implement the Mdui Logo in a multilingual fashion');
        $logo = Logo::fromJson(['url' => 'https://foobar.example.com']);
        $logo->translate('en');
    }

    public function test_primary_language_is_en()
    {
        $logo = Logo::fromJson(['url' => 'https://foobar.example.com']);
        $lang = $logo->getConfiguredLanguages();
        self::assertEquals(['en'], $lang);

    }

    public function provideCorrectJson()
    {
        return [
            'all data present' => ['{"url": "https://logo.example.com/logo.gif", "height": 12, "width": 50}'],
            'only url set' => ['{"url": "https://logo.example.com/logo.gif"}'],
            'only logo and width' => ['{"url": "https://logo.example.com/logo.gif", "width": 50}'],
            'only logo and height' => ['{"url": "https://logo.example.com/logo.gif", "height": 50}'],
            'additional data is ignored' => ['{"url": "https://logo.example.com/logo.gif", "height": 50, "widtf": 50}'],
            'url is not validated' => ['{"url": "you are elle", "height": 50, "width": 50}'],
        ];
    }

    public function provideUrlMissingJson()
    {
        return [
            'url not present' => ['{"height": 12, "width": 50}'],
            'url misspelled' => ['{"ulr": "you are elle", "height": 12, "width": 50}'],
        ];
    }
}
