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
use OpenConext\TestDataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FeatureTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @param mixed $notStringOrEmtpyString
     */
    #[DataProviderExternal(TestDataProvider::class, 'notStringOrEmptyString')]
    #[Group('EngineBlockBundle')]
    #[Group('Configuration')]
    #[Test]
    public function feature_key_is_required_to_be_a_non_empty_string($notStringOrEmtpyString)
    {
        $this->expectException(InvalidArgumentException::class);

        new Feature($notStringOrEmtpyString, true);
    }

    /**
     * @param mixed $notBoolean
     */
    #[DataProviderExternal(TestDataProvider::class, 'notBoolean')]
    #[Group('EngineBlockBundle')]
    #[Group('Configuration')]
    #[Test]
    public function is_enabled_must_be_a_boolean($notBoolean)
    {
        $this->expectException(InvalidArgumentException::class);

        new Feature('some.feature', $notBoolean);
    }

    #[Group('EngineBlockBundle')]
    #[Group('Configuration')]
    #[Test]
    public function an_feature_created_as_enabled_is_enabled()
    {
        $feature = new Feature('some.feature', true);

        $this->assertTrue($feature->isEnabled());
    }

    #[Group('EngineBlockBundle')]
    #[Group('Configuration')]
    #[Test]
    public function an_feature_created_as_disabled_is_disabled()
    {
        $feature = new Feature('some.feature', false);

        $this->assertFalse($feature->isEnabled());
    }

    #[Group('EngineBlockBundle')]
    #[Group('Configuration')]
    #[Test]
    public function the_feature_key_can_be_retrieved()
    {
        $featureKey = 'some.feature.key';
        $feature = new Feature($featureKey, true);

        $this->assertSame($featureKey, $feature->getFeatureKey());
    }
}
