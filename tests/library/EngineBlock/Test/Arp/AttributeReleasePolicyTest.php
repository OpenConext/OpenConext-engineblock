<?php

/**
 * Copyright 2024 SURFnet B.V.
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

use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use PHPUnit\Framework\TestCase;

class EngineBlock_Test_Arp_AttributeReleasePolicyTest extends TestCase
{
    public function testEnforceNumericArpKeyException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid release as for attribute "urn:mace:dir:attribute-def:cn", attribute cannot be numeric, got: "9999"');

        $arp = array(
            'urn:mace:dir:attribute-def:cn' => array(
                array(
                    "value" => "*",
                    "release_as" => "9999",
                ),
            ),
        );

        $policy = new AttributeReleasePolicy($arp);
    }
}
