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
use PHPUnit\Framework\TestCase;

class FeatureTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     * @dataProvider \OpenConext\TestDataProvider::notStringOrEmptyString
     * @param mixed $notStringOrEmtpyString
     */
    public function feature_key_is_required_to_be_a_non_empty_string($notStringOrEmtpyString)
    {
        $this->expectException(InvalidArgumentException::class);

        new Feature($notStringOrEmtpyString, true);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     * @dataProvider \OpenConext\TestDataProvider::notBoolean
     * @param mixed $notBoolean
     */
    public function is_enabled_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new Feature('some.feature', $notBoolean);
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function an_feature_created_as_enabled_is_enabled()
    {
        $feature = new Feature('some.feature', true);

        $this->assertTrue($feature->isEnabled());
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function an_feature_created_as_disabled_is_disabled()
    {
        $feature = new Feature('some.feature', false);

        $this->assertFalse($feature->isEnabled());
    }

    /**
     * @test
     * @group EngineBlockBundle
     * @group Configuration
     */
    public function the_feature_key_can_be_retrieved()
    {
        $featureKey = 'some.feature.key';
        $feature = new Feature($featureKey, true);

        $this->assertSame($featureKey, $feature->getFeatureKey());
    }
}
