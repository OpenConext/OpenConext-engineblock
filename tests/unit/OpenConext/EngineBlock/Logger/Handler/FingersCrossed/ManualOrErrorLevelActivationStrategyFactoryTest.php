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

namespace OpenConext\EngineBlock\Logger\Handler\FingersCrossed;

use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ManualOrErrorLevelActivationStrategyFactoryTest extends TestCase
{
    /**
     * @test
     * @group EngineBlock
     * @group Logger
     */
    public function factory_creates_a_manual_or_decorated_activation_strategy()
    {
        $this->expectNotToPerformAssertions();
        ManualOrErrorLevelActivationStrategyFactory::createActivationStrategy(['action_level' => 'INFO']);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Logger
     *
     * @dataProvider configurationDataProvider
     *
     * @param array $config
     * @param string $expectedExceptionMessageContains
     */
    public function configuration_is_validated(array $config, $expectedExceptionMessageContains)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessageContains);

        ManualOrErrorLevelActivationStrategyFactory::createActivationStrategy($config);
    }

    public function configurationDataProvider()
    {
        return [
            'no action level'      => [
                [],
                'Missing configuration value'
            ],
            'invalid action level' => [
                ['action_level' => 'INVALID'],
                'Configured action level must be a valid PSR-compliant log level'
            ],
        ];
    }
}
