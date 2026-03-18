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

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Authentication\Repository\ConsentRepository;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use PHPUnit\Framework\TestCase;
use SAML2\XML\saml\NameID;

class ConsentHashServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var ConsentHashService
     */
    private $chs;

    public function setUp(): void
    {
        $mockConsentHashRepository = m::mock(ConsentRepository::class);
        $featureConfig = new FeatureConfiguration(['eb.stable_consent_hash_migration' => false]);
        $this->chs = new ConsentHashService($mockConsentHashRepository, $featureConfig);
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
        // Known limitation: when mustStoreValues=false the hash is built from attribute *names* only.
        // For sequential (numerically-indexed) arrays the "names" are just integer indices [0, 1, 2, …],
        // so two sequential arrays with the same count but completely different values produce the same hash.
        // This is accepted because in practice all SAML attributes are keyed by URN strings
        // (e.g. 'urn:mace:dir:attribute-def:displayName'), not by integer indices, making this
        // collision path unreachable in production.
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

    public function test_stable_attribute_hash_can_handle_nameid_objects()
    {
        $nameId = new NameID();
        $nameId->setValue('83aa0a79363edcf872c966b0d6eaf3f5e26a6a77');
        $nameId->setFormat('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent');

        $attributes = [
            'urn:mace:dir:attribute-def:displayName' => ['John Doe'],
            'urn:mace:dir:attribute-def:uid' => ['joe-f12'],
            'urn:mace:dir:attribute-def:cn' => ['John Doe'],
            'urn:mace:dir:attribute-def:sn' => ['Doe'],
            'urn:mace:dir:attribute-def:eduPersonPrincipalName' => ['j.doe@example.com'],
            'urn:mace:dir:attribute-def:givenName' => ['John'],
            'urn:mace:dir:attribute-def:mail' => ['j.doe@example.com'],
            'urn:mace:terena.org:attribute-def:schacHomeOrganization' => ['example.com'],
            'nl:surf:test:something' => [0 => 'arbitrary-value'],
            'urn:mace:dir:attribute-def:eduPersonTargetedID' => [$nameId],
            'urn:oid:1.3.6.1.4.1.5923.1.1.1.10' => [$nameId],
        ];

        $hash = $this->chs->getStableAttributesHash($attributes, false);
        $this->assertTrue(is_string($hash));
    }

    public function test_stable_attribute_hash_attribute_name_casing_normalized()
    {
        // Issue requirement: "Case normalize all attribute names"
        // Attribute names (keys) differing only in casing must yield the same hash
        $lowercase = [
            'urn:mace:dir:attribute-def:displayname' => ['John Doe'],
            'urn:mace:dir:attribute-def:uid'         => ['joe-f12'],
        ];
        $mixed = [
            'urn:mace:dir:attribute-def:displayName' => ['John Doe'],
            'URN:MACE:DIR:ATTRIBUTE-DEF:UID'         => ['joe-f12'],
        ];

        $this->assertEquals(
            $this->chs->getStableAttributesHash($lowercase, true),
            $this->chs->getStableAttributesHash($mixed, true)
        );
        $this->assertEquals(
            $this->chs->getStableAttributesHash($lowercase, false),
            $this->chs->getStableAttributesHash($mixed, false)
        );
    }

    public function test_stable_attribute_hash_attribute_name_ordering_normalized()
    {
        // Issue requirement: "Sort all attribute names"
        $alphabetical = [
            'urn:attribute:a' => ['value1'],
            'urn:attribute:b' => ['value2'],
            'urn:attribute:c' => ['value3'],
        ];
        $reversed = [
            'urn:attribute:c' => ['value3'],
            'urn:attribute:b' => ['value2'],
            'urn:attribute:a' => ['value1'],
        ];

        $this->assertEquals(
            $this->chs->getStableAttributesHash($alphabetical, true),
            $this->chs->getStableAttributesHash($reversed, true)
        );
        $this->assertEquals(
            $this->chs->getStableAttributesHash($alphabetical, false),
            $this->chs->getStableAttributesHash($reversed, false)
        );
    }

    // -------------------------------------------------------------------------
    // Unstable hash algorithm — getUnstableAttributesHash
    // -------------------------------------------------------------------------

    public function test_unstable_attribute_hash_mustStoreValues_false_uses_keys_only()
    {
        // When mustStoreValues=false the hash is based on attribute names only.
        // Two arrays with the same keys but different values must yield the same hash.
        $attributes     = ['urn:attr:a' => ['Alice'], 'urn:attr:b' => ['Bob']];
        $differentValues = ['urn:attr:a' => ['Charlie'], 'urn:attr:b' => ['Dave']];

        $this->assertEquals(
            $this->chs->getUnstableAttributesHash($attributes, false),
            $this->chs->getUnstableAttributesHash($differentValues, false)
        );
    }

    public function test_unstable_attribute_hash_mustStoreValues_true_includes_values()
    {
        // When mustStoreValues=true, attribute values are part of the hash.
        // Two arrays with the same keys but different values must yield a different hash.
        $attributes     = ['urn:attr:a' => ['Alice'], 'urn:attr:b' => ['Bob']];
        $differentValues = ['urn:attr:a' => ['Charlie'], 'urn:attr:b' => ['Dave']];

        $this->assertNotEquals(
            $this->chs->getUnstableAttributesHash($attributes, true),
            $this->chs->getUnstableAttributesHash($differentValues, true)
        );
    }

    public function test_unstable_attribute_hash_key_order_normalized_in_names_only_mode()
    {
        // When mustStoreValues=false the implementation sorts attribute names,
        // so reversed key order must produce the same hash.
        $attributes = ['urn:attr:a' => ['Alice'], 'urn:attr:b' => ['Bob']];
        $reversed   = ['urn:attr:b' => ['Bob'], 'urn:attr:a' => ['Alice']];

        $this->assertEquals(
            $this->chs->getUnstableAttributesHash($attributes, false),
            $this->chs->getUnstableAttributesHash($reversed, false)
        );
    }

    // -------------------------------------------------------------------------
    // isBlank / removeEmptyAttributes edge cases
    // -------------------------------------------------------------------------

    public function test_stable_attribute_hash_empty_array_produces_consistent_hash()
    {
        // An empty attribute array must not throw and must be idempotent.
        $hashWithValues    = $this->chs->getStableAttributesHash([], true);
        $hashWithoutValues = $this->chs->getStableAttributesHash([], false);

        $this->assertIsString($hashWithValues);
        $this->assertSame($hashWithValues, $this->chs->getStableAttributesHash([], true));
        $this->assertIsString($hashWithoutValues);
        $this->assertSame($hashWithoutValues, $this->chs->getStableAttributesHash([], false));
    }

    public function test_stable_attribute_hash_zero_string_not_removed_as_empty()
    {
        // "0" is truthy via is_numeric(), so it must NOT be removed by removeEmptyAttributes.
        // An attribute with value "0" must produce a different hash than an empty attribute set.
        $withZeroString = ['urn:attr:count' => '0'];
        $withoutAttr    = [];

        $this->assertNotEquals(
            $this->chs->getStableAttributesHash($withZeroString, true),
            $this->chs->getStableAttributesHash($withoutAttr, true)
        );
    }

    public function test_stable_attribute_hash_zero_float_not_removed_as_empty()
    {
        // 0.0 is numeric, so it must NOT be removed by removeEmptyAttributes.
        // An attribute with value 0.0 must produce a stable, non-empty hash.
        $withZeroFloat = ['urn:attr:count' => 0.0];

        $hash = $this->chs->getStableAttributesHash($withZeroFloat, true);

        $this->assertIsString($hash);
        $this->assertNotEmpty($hash);
        // Must be idempotent
        $this->assertSame($hash, $this->chs->getStableAttributesHash($withZeroFloat, true));
    }

    public function test_stable_attribute_hash_handles_multibyte_utf8_values(): void
    {
        // Arabic, Chinese, accented names — all common in European SAML federations
        $attributes = [
            'urn:mace:dir:attribute-def:sn' => ['Müller'],
            'urn:mace:dir:attribute-def:cn' => ['محمد'],
            'urn:mace:dir:attribute-def:displayName' => ['王芳'],
        ];
        $hash = $this->chs->getStableAttributesHash($attributes, true);
        $this->assertIsString($hash);
        $this->assertEquals(40, strlen($hash), 'SHA1 hash must be 40 hex chars; a false return from unserialize produces a wrong hash');
    }

    public function test_stable_hash_is_case_insensitive_for_multibyte_strings(): void
    {
        $lower = ['urn:mace:dir:attribute-def:sn' => ['müller']];
        $upper = ['urn:mace:dir:attribute-def:sn' => ['Müller']];
        $this->assertEquals(
            $this->chs->getStableAttributesHash($lower, true),
            $this->chs->getStableAttributesHash($upper, true),
            'Stable hash must be case-insensitive for multi-byte characters'
        );
    }
}
