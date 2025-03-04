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

namespace OpenConext\EngineBlockBundle\Tests;

use OpenConext\EngineBlockBundle\Exception\InvalidSbsResponseException;
use OpenConext\EngineBlockBundle\Sbs\EntitlementsResponse;
use PHPUnit\Framework\TestCase;

class EntitlementsResponseTest extends TestCase
{
    public function testFromDataValidAttributes()
    {
        $jsonData = ['attributes' => ['key1' => 'value1', 'key2' => 'value2']];

        $response = EntitlementsResponse::fromData($jsonData);

        $this->assertInstanceOf(EntitlementsResponse::class, $response);
        $this->assertEquals($jsonData['attributes'], $response->attributes);
    }

    public function testFromDataMissingAttributes()
    {
        $this->expectException(InvalidSbsResponseException::class);
        $this->expectExceptionMessage('Key: Attributes was not found in the SBS entitlements response');

        $jsonData = ['someOtherKey' => []];
        EntitlementsResponse::fromData($jsonData);
    }

    public function testFromDataAttributesNotArray()
    {
        $this->expectException(InvalidSbsResponseException::class);
        $this->expectExceptionMessage('Key: Attributes was not found in the SBS entitlements response');

        $jsonData = ['attributes' => 'not_an_array'];
        EntitlementsResponse::fromData($jsonData);
    }
}
