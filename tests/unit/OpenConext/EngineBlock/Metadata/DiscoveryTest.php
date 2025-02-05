<?php

/**
 * Copyright 2025 SURFnet B.V.
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

use OpenConext\EngineBlock\Exception\InvalidDiscoveryException;
use PHPUnit\Framework\TestCase;

class DiscoveryTest extends TestCase
{
    public function test_successful_create(): void
    {
        $discovery = Discovery::create(['en' => 'foo'], [], null);
        $this->assertNotNull($discovery);
    }

    /**
     * @dataProvider localeValueArrayProvider
     */
    public function test_validates_localized_names(array $names, string $expectedExceptionMessage = null): void
    {
        $this->expectException(InvalidDiscoveryException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        Discovery::create($names, [], null);
    }

    /**
     * @dataProvider localeValueArrayProvider
     */
    public function test_validates_localized_keywords(array $keywords, string $expectedExceptionMessage): void
    {
        $this->expectException(InvalidDiscoveryException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        Discovery::create([], $keywords, null);
    }

    public function localeValueArrayProvider(): array
    {
        return [
            'non-string locale key' => [
                [1 => 'Some content'],
                "Discovery language key must be a string, '1' given"
            ],
            'locale key too long' => [
                ['eng' => 'English content'],
                "Invalid discovery language key, 'eng' given"
            ],
            'locale key too short' => [
                ['e' => 'English content'],
                "Invalid discovery language key, 'e' given"
            ],
            'non-string value' => [
                ['en' => 123],
                "Discovery value must be a string, '123' given"
            ],
            'empty array' => [
                [],
                "The Discovery does not have a required english name."
            ]
        ];
    }


}
