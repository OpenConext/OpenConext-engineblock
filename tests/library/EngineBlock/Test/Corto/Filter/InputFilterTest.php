<?php

/**
 * Copyright 2026 SURFnet B.V.
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

use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfigurationInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests the command selection logic in EngineBlock_Corto_Filter_Input.
 *
 * Verifies that when eb.run_all_manipulations_prior_to_consent is enabled,
 * the Input filter includes the Output filter's commands (pre-consent).
 */
class EngineBlock_Test_Corto_Filter_InputFilterTest extends TestCase
{
    private function buildFilter(bool $featureEnabled): EngineBlock_Corto_Filter_Input
    {
        $server = Phake::mock(EngineBlock_Corto_ProxyServer::class);
        $featureConfig = new FeatureConfiguration([
            'eb.run_all_manipulations_prior_to_consent' => $featureEnabled,
            'eb.block_user_on_violation'                => true,
        ]);

        return new class ($server, $featureConfig) extends EngineBlock_Corto_Filter_Input {
            private FeatureConfigurationInterface $featureConfig;

            public function __construct(EngineBlock_Corto_ProxyServer $server, FeatureConfigurationInterface $featureConfig)
            {
                parent::__construct($server);
                $this->featureConfig = $featureConfig;
            }

            protected function resolveFeatureConfiguration(): FeatureConfigurationInterface
            {
                return $this->featureConfig;
            }
        };
    }

    public function testGetCommandsFeatureEnabledReturnsMoreCommandsThanFeatureDisabled(): void
    {
        $featureOff = $this->buildFilter(false)->getCommands();
        $featureOn  = $this->buildFilter(true)->getCommands();

        self::assertGreaterThan(
            count($featureOff),
            count($featureOn),
            'Feature-enabled must add output commands to the input filter'
        );
    }
}
