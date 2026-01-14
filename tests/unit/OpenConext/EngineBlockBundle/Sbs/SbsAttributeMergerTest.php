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

use OpenConext\EngineBlockBundle\Sbs\SbsAttributeMerger;
use PHPUnit\Framework\TestCase;

class SbsAttributeMergerTest extends TestCase
{
    public function testMergeAttributesSuccessfully(): void
    {
        $allowedAttributes = [
            'eduPersonEntitlement',
            'eduPersonPrincipalName',
            'uuid',
            'sshkey'
        ];
        $merger = new SbsAttributeMerger($allowedAttributes);

        $samlAttributes = [
            "uuid" => '1234',
            "eduPersonEntitlement" => ["user_aff1@test.nl"],
            "eduPersonPrincipalName" => ["test_user@test.nl"],
            "uid" => ["test_user"],
            "original" => ['bar', 'soap'],
            "myString" => 'foobar',
        ];

        $sbsAttributes = [
            "uuid" => '5678',
            "eduPersonEntitlement" => ["user_aff2@test.nl"],
            "eduPersonPrincipalName" => ["test_user@test.nl"],
            "sshkey" => ["ssh_key1", "ssh_key2"]
        ];

        $expectedResult = [
            "uuid" => '5678',
            "eduPersonEntitlement" => ["user_aff1@test.nl", "user_aff2@test.nl"],
            "eduPersonPrincipalName" => ["test_user@test.nl"],
            "uid" => ["test_user"],
            "sshkey" => ["ssh_key1", "ssh_key2"],
            "original" => ['bar', 'soap'],
            "myString" => 'foobar',
        ];

        $this->assertEquals($expectedResult, $merger->mergeAttributes($samlAttributes, $sbsAttributes));
    }

    public function testMergeAttributesWithInvalidKeysThrowsException(): void
    {
        $allowedAttributes = [
            'email',
            'name'
        ];
        $merger = new SbsAttributeMerger($allowedAttributes);

        $samlAttributes = [
            'email' => ['user@example.com'],
            'role' => ['admin']
        ];

        $sbsAttributes = [
            'role' => ['user']
        ];

        $expectedResult = [
            'email' => ['user@example.com'],
            'role' => ['admin']
        ];

        $this->assertEquals($expectedResult, $merger->mergeAttributes($samlAttributes, $sbsAttributes));
    }

    public function testMergeAttributesWithEmptySbsAttributes(): void
    {
        $allowedAttributes = [
            'email',
            'name'
        ];
        $merger = new SbsAttributeMerger($allowedAttributes);

        $samlAttributes = [
            'email' => ['user@example.com'],
            'role' => ['admin']
        ];

        $sbsAttributes = [];

        $this->assertEquals($samlAttributes, $merger->mergeAttributes($samlAttributes, $sbsAttributes));
    }
}
