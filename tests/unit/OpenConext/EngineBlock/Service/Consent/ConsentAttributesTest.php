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
use SAML2\XML\saml\NameID;

class ConsentAttributesTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Strategy distinction
    // -------------------------------------------------------------------------

    public function test_with_values_and_names_only_produce_different_compare_values_for_same_input(): void
    {
        $raw = [
            'urn:mace:dir:attribute-def:displayName' => ['John Doe'],
            'urn:mace:dir:attribute-def:uid'         => ['joe-f12'],
        ];

        $this->assertNotSame(
            ConsentAttributes::withValues($raw)->getCompareValue(),
            ConsentAttributes::namesOnly($raw)->getCompareValue(),
            'withValues and namesOnly must produce different compare values for the same non-empty input'
        );
    }

    // -------------------------------------------------------------------------
    // Order invariance — withValues
    // -------------------------------------------------------------------------

    public function test_with_values_is_order_invariant_for_attribute_keys(): void
    {
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

        $this->assertSame(
            ConsentAttributes::withValues($alphabetical)->getCompareValue(),
            ConsentAttributes::withValues($reversed)->getCompareValue()
        );
    }

    public function test_with_values_is_order_invariant_for_attribute_values(): void
    {
        $forward  = ['urn:attribute:a' => ['alice', 'bob', 'charlie']];
        $reversed = ['urn:attribute:a' => ['charlie', 'bob', 'alice']];

        $this->assertSame(
            ConsentAttributes::withValues($forward)->getCompareValue(),
            ConsentAttributes::withValues($reversed)->getCompareValue()
        );
    }

    // -------------------------------------------------------------------------
    // Order invariance — namesOnly
    // -------------------------------------------------------------------------

    public function test_names_only_is_order_invariant_for_attribute_keys(): void
    {
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

        $this->assertSame(
            ConsentAttributes::namesOnly($alphabetical)->getCompareValue(),
            ConsentAttributes::namesOnly($reversed)->getCompareValue()
        );
    }

    // -------------------------------------------------------------------------
    // Case normalisation — withValues
    // -------------------------------------------------------------------------

    public function test_with_values_normalises_key_casing(): void
    {
        $lower = ['urn:mace:dir:attribute-def:displayname' => ['John Doe']];
        $upper = ['URN:MACE:DIR:ATTRIBUTE-DEF:DISPLAYNAME' => ['John Doe']];

        $this->assertSame(
            ConsentAttributes::withValues($lower)->getCompareValue(),
            ConsentAttributes::withValues($upper)->getCompareValue()
        );
    }

    public function test_with_values_normalises_value_casing(): void
    {
        $lower = ['urn:mace:dir:attribute-def:sn' => ['doe']];
        $upper = ['urn:mace:dir:attribute-def:sn' => ['DOE']];

        $this->assertSame(
            ConsentAttributes::withValues($lower)->getCompareValue(),
            ConsentAttributes::withValues($upper)->getCompareValue()
        );
    }

    public function test_with_values_normalises_multibyte_value_casing(): void
    {
        $lower = ['urn:mace:dir:attribute-def:sn' => ['müller']];
        $upper = ['urn:mace:dir:attribute-def:sn' => ['Müller']];

        $this->assertSame(
            ConsentAttributes::withValues($lower)->getCompareValue(),
            ConsentAttributes::withValues($upper)->getCompareValue(),
            'Case normalisation must handle multi-byte UTF-8 characters'
        );
    }

    // -------------------------------------------------------------------------
    // Case normalisation — namesOnly
    // -------------------------------------------------------------------------

    public function test_names_only_normalises_key_casing(): void
    {
        $lower = ['urn:mace:dir:attribute-def:displayname' => ['John Doe']];
        $upper = ['URN:MACE:DIR:ATTRIBUTE-DEF:DISPLAYNAME' => ['John Doe']];

        $this->assertSame(
            ConsentAttributes::namesOnly($lower)->getCompareValue(),
            ConsentAttributes::namesOnly($upper)->getCompareValue()
        );
    }

    // -------------------------------------------------------------------------
    // Empty attribute stripping — withValues
    // -------------------------------------------------------------------------

    public function test_with_values_strips_empty_array_attribute(): void
    {
        $withEmpty    = ['urn:attr:a' => ['value'], 'urn:attr:b' => []];
        $withoutEmpty = ['urn:attr:a' => ['value']];

        $this->assertSame(
            ConsentAttributes::withValues($withEmpty)->getCompareValue(),
            ConsentAttributes::withValues($withoutEmpty)->getCompareValue()
        );
    }

    public function test_with_values_losing_an_attribute_changes_compare_value(): void
    {
        $withValue = ['urn:mace:dir:attribute-def:displayName' => ['John Doe']];
        $withEmpty = ['urn:mace:dir:attribute-def:displayName' => []];

        $this->assertNotSame(
            ConsentAttributes::withValues($withValue)->getCompareValue(),
            ConsentAttributes::withValues($withEmpty)->getCompareValue(),
            'An attribute going from a value to empty must change the compare value'
        );
    }

    public function test_with_values_strips_stray_empty_sub_value(): void
    {
        $withStray    = ['urn:mace:dir:attribute-def:displayName' => ['John Doe', '']];
        $withoutStray = ['urn:mace:dir:attribute-def:displayName' => ['John Doe']];

        $this->assertSame(
            ConsentAttributes::withValues($withStray)->getCompareValue(),
            ConsentAttributes::withValues($withoutStray)->getCompareValue(),
            'Stray empty sub-values must be stripped and not affect the compare value'
        );
    }

    // -------------------------------------------------------------------------
    // Empty attribute stripping — namesOnly
    // -------------------------------------------------------------------------

    public function test_names_only_strips_empty_array_attribute(): void
    {
        $withEmpty    = ['urn:attr:a' => ['value'], 'urn:attr:b' => []];
        $withoutEmpty = ['urn:attr:a' => ['value']];

        $this->assertSame(
            ConsentAttributes::namesOnly($withEmpty)->getCompareValue(),
            ConsentAttributes::namesOnly($withoutEmpty)->getCompareValue()
        );
    }

    public function test_names_only_losing_an_attribute_changes_compare_value(): void
    {
        $withValue = ['urn:mace:dir:attribute-def:displayName' => ['John Doe']];
        $withEmpty = ['urn:mace:dir:attribute-def:displayName' => []];

        $this->assertNotSame(
            ConsentAttributes::namesOnly($withValue)->getCompareValue(),
            ConsentAttributes::namesOnly($withEmpty)->getCompareValue(),
            'An attribute going from a value to empty must change the compare value in namesOnly mode'
        );
    }

    // -------------------------------------------------------------------------
    // Zero values must NOT be stripped — withValues
    // -------------------------------------------------------------------------

    public function test_with_values_zero_string_not_stripped(): void
    {
        $withZero    = ['urn:attr:count' => '0'];
        $withoutAttr = [];

        $this->assertNotSame(
            ConsentAttributes::withValues($withZero)->getCompareValue(),
            ConsentAttributes::withValues($withoutAttr)->getCompareValue()
        );
    }

    public function test_with_values_zero_int_not_stripped(): void
    {
        $withZero    = ['urn:attr:count' => 0];
        $withoutAttr = [];

        $this->assertNotSame(
            ConsentAttributes::withValues($withZero)->getCompareValue(),
            ConsentAttributes::withValues($withoutAttr)->getCompareValue()
        );
    }

    public function test_with_values_zero_float_not_stripped(): void
    {
        $withZero    = ['urn:attr:count' => 0.0];
        $withoutAttr = [];

        $this->assertNotSame(
            ConsentAttributes::withValues($withZero)->getCompareValue(),
            ConsentAttributes::withValues($withoutAttr)->getCompareValue()
        );
    }

    // -------------------------------------------------------------------------
    // NameID handling — withValues
    // -------------------------------------------------------------------------

    public function test_with_values_handles_nameid_objects(): void
    {
        $nameId = new NameID();
        $nameId->setValue('83aa0a79363edcf872c966b0d6eaf3f5e26a6a77');
        $nameId->setFormat('urn:oasis:names:tc:SAML:2.0:nameid-format:persistent');

        $attributes = [
            'urn:mace:dir:attribute-def:uid'              => ['joe-f12'],
            'urn:mace:dir:attribute-def:eduPersonTargetedID' => [$nameId],
        ];

        // Must not throw and must return a non-empty string
        $compareValue = ConsentAttributes::withValues($attributes)->getCompareValue();
        $this->assertNotEmpty($compareValue);
    }

    // -------------------------------------------------------------------------
    // Non-mutation
    // -------------------------------------------------------------------------

    public function test_with_values_does_not_mutate_input(): void
    {
        $raw = [
            'urn:mace:dir:attribute-def:DisplayName' => ['John Doe'],
            'urn:mace:dir:attribute-def:empty'       => [],
            'urn:mace:dir:attribute-def:isMemberOf'  => [2 => 'urn:collab:org:vm.openconext.ORG', 0 => 'urn:collab:org:vm.openconext.org'],
        ];
        $snapshot = $raw;

        ConsentAttributes::withValues($raw);

        $this->assertSame($snapshot, $raw, 'withValues must not mutate the input array');
    }

    public function test_names_only_does_not_mutate_input(): void
    {
        $raw = [
            'urn:mace:dir:attribute-def:DisplayName' => ['John Doe'],
            'urn:mace:dir:attribute-def:empty'       => [],
        ];
        $snapshot = $raw;

        ConsentAttributes::namesOnly($raw);

        $this->assertSame($snapshot, $raw, 'namesOnly must not mutate the input array');
    }

    // -------------------------------------------------------------------------
    // Sparse index normalisation
    // -------------------------------------------------------------------------

    public function test_with_values_sparse_sequential_indexes_are_normalised(): void
    {
        $dense  = ['urn:attr:a' => [0 => 'aap', 1 => 'noot']];
        $sparse = ['urn:attr:a' => [0 => 'aap', 2 => 'noot']];

        $this->assertSame(
            ConsentAttributes::withValues($dense)->getCompareValue(),
            ConsentAttributes::withValues($sparse)->getCompareValue()
        );
    }
}
