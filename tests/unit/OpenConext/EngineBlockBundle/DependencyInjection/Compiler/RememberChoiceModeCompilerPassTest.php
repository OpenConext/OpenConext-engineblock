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

declare(strict_types=1);

namespace Tests\OpenConext\EngineBlockBundle\DependencyInjection\Compiler;

use OpenConext\EngineBlockBundle\DependencyInjection\Compiler\RememberChoiceModeCompilerPass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RememberChoiceModeCompilerPassTest extends TestCase
{
    #[DataProvider('validCombinationProvider')]
    public function testAllowsAtMostOneRememberChoiceMode(?bool $global, ?bool $perIdp): void
    {
        $container = $this->containerWith($global, $perIdp);

        (new RememberChoiceModeCompilerPass())->process($container);

        $this->addToAssertionCount(1);
    }

    public static function validCombinationProvider(): array
    {
        return [
            'both disabled' => [false, false],
            'only global enabled' => [true, false],
            'only per-idp enabled' => [false, true],
            'parameters absent' => [null, null],
            'global enabled, per-idp absent' => [true, null],
            'per-idp enabled, global absent' => [null, true],
        ];
    }

    public function testRejectsBothRememberChoiceModesEnabled(): void
    {
        $container = $this->containerWith(true, true);

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('mutually exclusive');
        $this->expectExceptionMessage('wayf.remember_choice');
        $this->expectExceptionMessage('feature_enable_wayf_remember_choice_per_idp');

        (new RememberChoiceModeCompilerPass())->process($container);
    }

    private function containerWith(?bool $global, ?bool $perIdp): ContainerBuilder
    {
        $container = new ContainerBuilder();

        if ($global !== null) {
            $container->setParameter(RememberChoiceModeCompilerPass::GLOBAL_PARAMETER, $global);
        }

        if ($perIdp !== null) {
            $container->setParameter(RememberChoiceModeCompilerPass::PER_IDP_PARAMETER, $perIdp);
        }

        return $container;
    }
}
