<?php

/**
 * Copyright 2014 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Http;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase as TestCase;

class ResourcePathFormatterTest extends TestCase
{
    /**
     * @test
     * @group EngineBlock
     * @group Http
     *
     * @dataProvider \OpenConext\TestDataProvider::notString()
     * @param $nonString
     */
    public function resource_path_formats_can_only_be_strings($nonString)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected to be string');

        ResourcePathFormatter::format($nonString, []);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Http
     */
    public function resource_parameters_are_formatted_correctly()
    {
        $resourcePathFormat = 'resource/%s/%d';
        $parameters = ['id', 2];

        $expectedFormattedResourcePath = 'resource/id/2';
        $actualFormattedResourcePath = ResourcePathFormatter::format($resourcePathFormat, $parameters);

        $this->assertSame($expectedFormattedResourcePath, $actualFormattedResourcePath);
    }
}
