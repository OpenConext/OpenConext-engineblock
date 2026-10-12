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

namespace OpenConext\EngineBlockBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RememberChoiceModeCompilerPass implements CompilerPassInterface
{
    public const GLOBAL_PARAMETER = 'wayf.remember_choice';
    public const PER_IDP_PARAMETER = 'feature_enable_wayf_remember_choice_per_idp';

    public function process(ContainerBuilder $container): void
    {
        if ($this->isEnabled($container, self::GLOBAL_PARAMETER)
            && $this->isEnabled($container, self::PER_IDP_PARAMETER)
        ) {
            throw new InvalidConfigurationException(sprintf(
                'The WAYF remember-my-choice modes are mutually exclusive: "%s" and "%s" cannot both be true. '
                . 'Enable only one of them.',
                self::GLOBAL_PARAMETER,
                self::PER_IDP_PARAMETER
            ));
        }
    }

    private function isEnabled(ContainerBuilder $container, string $parameter): bool
    {
        return $container->hasParameter($parameter)
            && filter_var($container->getParameter($parameter), FILTER_VALIDATE_BOOLEAN);
    }
}
