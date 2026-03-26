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
}
