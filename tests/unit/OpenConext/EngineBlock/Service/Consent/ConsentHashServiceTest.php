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

namespace OpenConext\EngineBlock\Service\Consent;

use PHPUnit\Framework\TestCase;

class ConsentHashServiceTest extends TestCase
{
    /**
     * @var ConsentHashService
     */
    private $chs;

    public function setUp()
    {
        $this->chs = new ConsentHashService();
    }

    public function test_stable_attribute_hash_switched_order_associative_array()
    {
        $attributes = [
            'urn:mace:dir:attribute-def:displayName' => ['John Doe'],
            'urn:mace:dir:attribute-def:uid' => ['joe-f12'],
            'urn:mace:dir:attribute-def:cn' => ['John Doe'],
            'urn:mace:dir:attribute-def:sn' => ['Doe'],
            'urn:mace:dir:attribute-def:eduPersonPrincipalName' => ['j.doe@example.com'],
            'urn:mace:dir:attribute-def:givenName' => ['John'],
            'urn:mace:dir:attribute-def:mail' => ['j.doe@example.com'],
            'urn:mace:terena.org:attribute-def:schacHomeOrganization' => ['example.com'],
            'urn:mace:dir:attribute-def:isMemberOf' => [
                      'urn:collab:org:vm.openconext.ORG',
                      'urn:collab:org:vm.openconext.ORG',
                      'urn:collab:org:vm.openconext.ORG',
                      'urn:collab:org:vm.openconext.org',
                      'urn:collaboration:organisation:vm.openconext.org',
                      'urn:collab:org:vm.openconext.org',
                      'urn:collab:org:vm.openconext.org',
                      'urn:collab:org:vm.openconext.org',
                  ],
        ];
        $attributesSwitchedOrder = [
            'urn:mace:dir:attribute-def:uid' => ['joe-f12'],
            'urn:mace:dir:attribute-def:displayName' => ['John Doe'],
            'urn:mace:dir:attribute-def:cn' => ['John Doe'],
            'urn:mace:dir:attribute-def:sn' => ['Doe'],
            'urn:mace:dir:attribute-def:eduPersonPrincipalName' => ['j.doe@example.com'],
            'urn:mace:dir:attribute-def:givenName' => ['John'],
            'urn:mace:dir:attribute-def:mail' => ['j.doe@example.com'],
            'urn:mace:terena.org:attribute-def:schacHomeOrganization' => ['example.com'],
            'urn:mace:dir:attribute-def:isMemberOf' => [
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.ORG',
                'urn:collaboration:organisation:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.ORG',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.ORG',
            ],
        ];
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, false), $this->chs->getStableAttributesHash($attributesSwitchedOrder, false));
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, true), $this->chs->getStableAttributesHash($attributesSwitchedOrder, true));
    }

    public function test_stable_attribute_hash_switched_order_sequential_array()
    {
        $attributes = [
            ['John Doe'],
            ['joe-f12'],
            ['John Doe'],
            ['Doe'],
            ['j.doe@example.com'],
            ['John'],
            ['j.doe@example.com'],
            ['example.com'],
            [
                'urn:collab:org:vm.openconext.ORG',
                'urn:collab:org:vm.openconext.ORG',
                'urn:collab:org:vm.openconext.ORG',
                'urn:collab:org:vm.openconext.org',
                'urn:collaboration:organisation:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
            ],
        ];
        $attributesSwitchedOrder = [
            ['John Doe'],
            ['John Doe'],
            ['joe-f12'],
            ['Doe'],
            ['j.doe@example.com'],
            ['John'],
            ['example.com'],
            ['j.doe@example.com'],
            [
                'urn:collaboration:organisation:vm.openconext.org',
                'urn:collab:org:vm.openconext.ORG',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.ORG',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.ORG',
            ],
        ];
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, false), $this->chs->getStableAttributesHash($attributesSwitchedOrder, false));
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, true), $this->chs->getStableAttributesHash($attributesSwitchedOrder, true));
    }

    public function test_stable_attribute_hash_switched_order_and_different_casing_associative_array()
    {
        $attributes = [
            'urn:mace:dir:attribute-def:displayName' => ['John Doe'],
            'urn:mace:dir:attribute-def:uid' => ['joe-f12'],
            'urn:mace:dir:attribute-def:cn' => ['John Doe'],
            'urn:mace:dir:attribute-def:sn' => ['Doe'],
            'urn:mace:dir:attribute-def:eduPersonPrincipalName' => ['j.doe@example.com'],
            'urn:mace:dir:attribute-def:givenName' => ['John'],
            'urn:mace:dir:attribute-def:mail' => ['j.doe@example.com'],
            'urn:mace:terena.org:attribute-def:schacHomeOrganization' => ['example.com'],
            'urn:mace:dir:attribute-def:isMemberOf' => [
                'urn:collab:org:vm.openconext.ORG',
                'urn:collab:org:vm.openconext.ORG',
                'urn:collab:org:vm.openconext.ORG',
                'urn:collab:org:vm.openconext.org',
                'urn:collaboration:organisation:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
            ],
        ];
        $attributesSwitchedOrderAndCasing = [
            'urn:mace:dir:attribute-def:sn' => ['DOE'],
            'urn:mace:dir:attribute-def:eduPersonPrincipalName' => ['j.doe@example.com'],
            'urn:mace:dir:attribute-def:givenName' => ['John'],
            'urn:mace:dir:attribute-def:CN' => ['John Doe'],
            'urn:mace:dir:attribute-def:mail' => ['j.doe@example.com'],
            'urn:mace:dir:attribute-DEF:displayName' => ['John Doe'],
            'urn:mace:terena.org:attribute-def:schacHomeOrganization' => ['example.com'],
            'urn:mace:dir:attribute-def:isMemberOf' => [
                'urn:collab:org:vm.openconext.ORG',
                'urn:collab:org:vm.openconext.ORG',
                'urn:collab:org:vm.openconext.ORG',
                'urn:collab:org:vm.openconext.org',
                'urn:collaboration:organisation:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
            ],
            'urn:mace:dir:attribute-def:UID' => ['joe-f12'],
        ];
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, false), $this->chs->getStableAttributesHash($attributesSwitchedOrderAndCasing, false));
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, true), $this->chs->getStableAttributesHash($attributesSwitchedOrderAndCasing, true));
    }

    public function test_stable_attribute_hash_switched_order_and_different_casing_sequential_array()
    {
        $attributes = [
            ['John Doe'],
            ['joe-f12'],
            ['John Doe'],
            ['Doe'],
            ['j.doe@example.com'],
            ['John'],
            ['j.doe@example.com'],
            ['example.com'],
            [
                'urn:collab:org:vm.openconext.ORG',
                'urn:collab2:org:vm.openconext.ORG',
                'urn:collab3:org:vm.openconext.ORG',
                'urn:collab:org:vm.openconext.org',
                'urn:collaboration:organisation:vm.openconext.org',
                'urn:collab1:org:vm.openconext.org',
                'urn:collab2:org:vm.openconext.org',
                'urn:collab3:org:vm.openconext.org',
            ],
        ];
        $attributesSwitchedOrderAndCasing = [
            ['joe-f12'],
            ['John Doe'],
            ['John Doe'],
            ['j.doe@example.com'],
            ['John'],
            ['EXample.com'],
            ['j.doe@example.com'],
            [
                'URN:collab2:org:vm.openconext.ORG',
                'urn:collab2:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.Org',
                'urn:collaboration:organisation:VM.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:collab3:org:vm.openconext.org',
                'urn:collab3:org:vm.openconext.ORG',
                'urn:collab1:org:vm.openconext.org',
            ],
            ['DOE'],
        ];
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, false), $this->chs->getStableAttributesHash($attributesSwitchedOrderAndCasing, false));
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, true), $this->chs->getStableAttributesHash($attributesSwitchedOrderAndCasing, true));
    }

    public function test_stable_attribute_hash_different_casing_associative_array()
    {
        $attributes = [
            'urn:mace:dir:attribute-def:displayName' => ['John Doe'],
            'urn:mace:dir:attribute-def:uid' => ['joe-f12'],
            'urn:mace:dir:attribute-def:cn' => ['John Doe'],
            'urn:mace:dir:attribute-def:sn' => ['Doe'],
            'urn:mace:dir:attribute-def:eduPersonPrincipalName' => ['j.doe@example.com'],
            'urn:mace:dir:attribute-def:givenName' => ['John'],
            'urn:mace:dir:attribute-def:mail' => ['j.doe@example.com'],
            'urn:mace:terena.org:attribute-def:schacHomeOrganization' => ['example.com'],
            'urn:mace:dir:attribute-def:isMemberOf' => [
                      'urn:collab:ORG:vm.openconext.ORG',
                      'urn:collab:ORG:vm.openconext.ORG',
                      'urn:collab:ORG:vm.openconext.ORG',
                      'urn:collab:org:vm.openconext.org',
                      'urn:collaboration:organisation:vm.openconext.org',
                      'urn:collab:org:vm.openconext.org',
                      'urn:collab:org:vm.openconext.org',
                      'urn:collab:org:vm.openconext.org',
                  ],
        ];
        $attributesDifferentCasing = [
            'urn:mace:dir:attribute-def:DISPLAYNAME' => ['John Doe'],
            'urn:mace:dir:attribute-def:uid' => ['joe-f12'],
            'urn:mace:dir:attribute-def:cn' => ['John Doe'],
            'urn:mace:dir:attribute-def:sn' => ['DOE'],
            'urn:mace:dir:attribute-def:eduPersonPrincipalName' => ['j.doe@example.com'],
            'urn:mace:dir:attribute-def:givenName' => ['John'],
            'urn:mace:dir:attribute-def:mail' => ['j.doe@example.com'],
            'urn:mace:terena.org:attribute-def:schacHomeOrganization' => ['example.com'],
            'urn:mace:dir:attribute-def:ISMemberOf' => [
                      'URN:collab:org:VM.openconext.org',
                      'URN:collab:org:VM.openconext.org',
                      'URN:collab:org:VM.openconext.org',
                      'urn:collab:org:vm.openconext.org',
                      'urn:collaboration:organisation:vm.openconext.org',
                      'urn:collab:org:vm.openconext.org',
                      'urn:collab:org:vm.openconext.org',
                      'urn:collab:org:vm.openconext.org',
                  ],
        ];
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, false), $this->chs->getStableAttributesHash($attributesDifferentCasing, false));
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, true), $this->chs->getStableAttributesHash($attributesDifferentCasing, true));
    }

    public function test_stable_attribute_hash_different_casing_sequential_array()
    {
        $attributes = [
            ['John Doe'],
            ['joe-f12'],
            ['John Doe'],
            ['Doe'],
            ['j.doe@example.com'],
            ['John'],
            ['j.doe@example.com'],
            ['example.com'],
            [
                'urn:collab:org:vm.openconext.ORG',
                'urn:collab:org:vm.openconext.ORG',
                'urn:collab:org:vm.openconext.ORG',
                'urn:collab:org:vm.openconext.org',
                'urn:collaboration:organisation:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
            ],
        ];
        $attributesDifferentCasing = [
            ['JOHN Doe'],
            ['joe-f12'],
            ['John DOE'],
            ['Doe'],
            ['j.doe@example.com'],
            ['John'],
            ['j.doe@example.com'],
            ['example.com'],
            [
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:VM.openconext.ORG',
                'urn:collab:org:VM.openconext.org',
                'urn:collaboration:organisation:VM.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:COLLAB:org:vm.openconext.org',
            ],
        ];
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, false), $this->chs->getStableAttributesHash($attributesDifferentCasing, false));
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, true), $this->chs->getStableAttributesHash($attributesDifferentCasing, true));
    }

    public function test_stable_attribute_hash_reordering_sparse_sequential_arrays()
    {
        $attributes = [ "AttributeA" => [ 0 => "aap", 1 => "noot"] ];
        $attributesDifferentCasing =
            [ "AttributeA" => [ 0 => "aap", 2 => "noot"] ];
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, false), $this->chs->getStableAttributesHash($attributesDifferentCasing, false));
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, true), $this->chs->getStableAttributesHash($attributesDifferentCasing, true));
    }

    public function test_stable_attribute_hash_remove_empty_attributes()
    {
        $attributes = [ "AttributeA" => [ 0 => "aap", 1 => "noot"], "AttributeB" => [], "AttributeC" => 0 ];
        $attributesDifferentNoEmptyValues =
            [ "AttributeA" => [ 0 => "aap", 2 => "noot"], "AttributeC" => 0 ];
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, false), $this->chs->getStableAttributesHash($attributesDifferentNoEmptyValues, false));
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, true), $this->chs->getStableAttributesHash($attributesDifferentNoEmptyValues, true));
    }

    public function test_stable_attribute_hash_two_different_arrays_yield_different_hashes_associative()
    {
        $attributes = [
            'a' => ['John Doe'],
            'b' => ['joe-f12'],
            'c' => ['John Doe'],
            'd' => ['Doe'],
            'e' => ['j.doe@example.com'],
            'f' => ['John'],
            'g' => ['j.doe@example.com'],
            'h' => ['example.com'],
        ];
        $differentAttributes = [
            'i' => 'urn:collab:org:vm.openconext.ORG',
            'j' => 'urn:collab:org:vm.openconext.ORG',
            'k' => 'urn:collab:org:vm.openconext.ORG',
            'l' => 'urn:collab:org:vm.openconext.org',
            'm' => 'urn:collaboration:organisation:vm.openconext.org',
            'n' => 'urn:collab:org:vm.openconext.org',
            'o' => 'urn:collab:org:vm.openconext.org',
            'p' => 'urn:collab:org:vm.openconext.org',
        ];
        $this->assertNotEquals($this->chs->getStableAttributesHash($attributes, false), $this->chs->getStableAttributesHash($differentAttributes, false));
        $this->assertNotEquals($this->chs->getStableAttributesHash($attributes, true), $this->chs->getStableAttributesHash($differentAttributes, true));
    }

    public function test_stable_attribute_hash_two_different_arrays_yield_different_hashes_sequential()
    {
        $attributes = [
            ['John Doe'],
            ['joe-f12'],
            ['John Doe'],
            ['Doe'],
            ['j.doe@example.com'],
            ['John'],
            ['j.doe@example.com'],
            ['example.com'],
        ];
        $differentAttributes = [
            'urn:collab:org:vm.openconext.ORG',
            'urn:collab:org:vm.openconext.ORG',
            'urn:collab:org:vm.openconext.ORG',
            'urn:collab:org:vm.openconext.org',
            'urn:collaboration:organisation:vm.openconext.org',
            'urn:collab:org:vm.openconext.org',
            'urn:collab:org:vm.openconext.org',
            'urn:collab:org:vm.openconext.org',
        ];
        // two sequential arrays with the same amount of attributes will yield the exact same hash if no values must be stored.  todo: check if we want this?
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, false), $this->chs->getStableAttributesHash($differentAttributes, false));
        $this->assertNotEquals($this->chs->getStableAttributesHash($attributes, true), $this->chs->getStableAttributesHash($differentAttributes, true));
    }

    public function test_stable_attribute_hash_multiple_value_vs_single_value_associative_array()
    {
        $attributes = [
            'urn:mace:dir:attribute-def:displayName' => ['John Doe'],
            'urn:mace:dir:attribute-def:uid' => ['joe-f12'],
            'urn:mace:dir:attribute-def:cn' => ['John Doe'],
            'urn:mace:dir:attribute-def:sn' => ['Doe'],
            'urn:mace:dir:attribute-def:eduPersonPrincipalName' => ['j.doe@example.com'],
            'urn:mace:dir:attribute-def:givenName' => ['John'],
            'urn:mace:dir:attribute-def:mail' => ['j.doe@example.com'],
            'urn:mace:terena.org:attribute-def:schacHomeOrganization' => ['example.com'],
            'urn:mace:dir:attribute-def:isMemberOf' => [
                'urn:collab:ORG:vm.openconext.ORG',
                'urn:collab:ORG:vm.openconext.ORG',
                'urn:collab:ORG:vm.openconext.ORG',
                'urn:collab:org:vm.openconext.org',
                'urn:collaboration:organisation:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
                'urn:collab:org:vm.openconext.org',
            ],
        ];
        $attributesSingleValue = [
            'urn:mace:dir:attribute-def:displayName' => ['John Doe'],
            'urn:mace:dir:attribute-def:uid' => ['joe-f12'],
            'urn:mace:dir:attribute-def:cn' => ['John Doe'],
            'urn:mace:dir:attribute-def:sn' => ['Doe'],
            'urn:mace:dir:attribute-def:eduPersonPrincipalName' => ['j.doe@example.com'],
            'urn:mace:dir:attribute-def:givenName' => ['John'],
            'urn:mace:dir:attribute-def:mail' => ['j.doe@example.com'],
            'urn:mace:terena.org:attribute-def:schacHomeOrganization' => ['example.com'],
            'urn:mace:dir:attribute-def:isMemberOf' => [
                'urn:collab:org:vm.openconext.org',
            ],
        ];
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, false), $this->chs->getStableAttributesHash($attributesSingleValue, false));
        $this->assertNotEquals($this->chs->getStableAttributesHash($attributes, true), $this->chs->getStableAttributesHash($attributesSingleValue, true));
    }

    public function test_stable_attribute_hash_multiple_value_vs_single_value_sequential_array()
    {
        $attributes = [
            ['John Doe'],
            ['joe-f12'],
            ['John Doe'],
            ['Doe'],
            ['j.doe@example.com'],
            ['John'],
            ['j.doe@example.com'],
            ['example.com', 'j.doe@example.com', 'jane'],
        ];
        $attributesSingleValue = [
            ['John Doe'],
            ['joe-f12'],
            ['John Doe'],
            ['Doe'],
            ['j.doe@example.com'],
            ['John'],
            ['j.doe@example.com'],
            ['example.com'],
        ];
        $this->assertEquals($this->chs->getStableAttributesHash($attributes, false), $this->chs->getStableAttributesHash($attributesSingleValue, false));
        $this->assertNotEquals($this->chs->getStableAttributesHash($attributes, true), $this->chs->getStableAttributesHash($attributesSingleValue, true));
    }
}
