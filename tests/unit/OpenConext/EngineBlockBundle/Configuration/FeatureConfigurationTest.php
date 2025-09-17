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

namespace OpenConext\EngineBlockBundle\Configuration;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Exception\LogicException;
use PHPUnit\Framework\TestCase;
use stdClass;

class FeatureConfigurationTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[\PHPUnit\Framework\Attributes\Group('EngineBlockBundle')]
    #[\PHPUnit\Framework\Attributes\Group('Configuration')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function all_features_must_be_an_instance_of_feature()
    {
        $features = [
            'some.feature' => true,
            'other.feature' => false,
            'foo' => new stdClass()
        ];

        $this->expectException(InvalidArgumentException::class);

        new FeatureConfiguration($features);
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlockBundle')]
    #[\PHPUnit\Framework\Attributes\Group('Configuration')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function all_features_must_have_a_string_key()
    {
        $features = [
            'some.feature'  => true,
            1 => false,
        ];

        $this->expectException(InvalidArgumentException::class);

        new FeatureConfiguration($features);
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlockBundle')]
    #[\PHPUnit\Framework\Attributes\Group('Configuration')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function a_feature_can_be_queried_for_presence()
    {
        $features = [
            'some.feature'  => true,
            'other.feature' => false
        ];

        $featureConfiguration = new FeatureConfiguration($features);

        $this->assertTrue($featureConfiguration->hasFeature('some.feature'));
        $this->assertTrue($featureConfiguration->hasFeature('other.feature'));
        $this->assertFalse($featureConfiguration->hasFeature('not.configured'));
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlockBundle')]
    #[\PHPUnit\Framework\Attributes\Group('Configuration')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function a_feature_is_correctly_reported_to_be_enabled()
    {
        $features = [
            'some.feature'  => true,
            'disabled.feature' => false
        ];

        $featureConfiguration = new FeatureConfiguration($features);

        $this->assertTrue($featureConfiguration->isEnabled('some.feature'));
        $this->assertFalse($featureConfiguration->isEnabled('disabled.feature'));
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlockBundle')]
    #[\PHPUnit\Framework\Attributes\Group('Configuration')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function querying_whether_a_not_configured_feature_is_enabled_causes_an_exception_to_be_thrown()
    {
        $features = [
            'some.feature'  => true,
            'other.feature' => false
        ];

        $featureConfiguration = new FeatureConfiguration($features);

        $this->expectException(LogicException::class);

        $featureConfiguration->isEnabled('not.configured');
    }
}
