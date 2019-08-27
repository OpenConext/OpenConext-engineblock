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

namespace OpenConext\EngineBlockBundle\DependencyInjection;

use OpenConext\EngineBlockBundle\Configuration\Feature;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class OpenConextEngineBlockExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->processConfiguration(new Configuration(), $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controllers.yml');
        $loader->load('event_listeners.yml');
        $loader->load('repositories.yml');
        $loader->load('services.yml');
        $loader->load('logging.yml');

        $loader->load('bridge.yml');
        $loader->load('bridge_event_listeners.yml');
        $loader->load('compat.yml');

        $this->overwriteDefaultLogger($container);
        $this->setUrlParameterBasedOnEnv($container);
        $this->setFeatureConfiguration($container, $configuration['features']);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function overwriteDefaultLogger(ContainerBuilder $container)
    {
        $container->removeAlias('logger');
        $container->setAlias('logger', 'monolog.logger.' . $container->getParameter('logger.channel'));
    }

    /**
     * @param ContainerBuilder $container
     */
    private function setUrlParameterBasedOnEnv(ContainerBuilder $container)
    {
        if (in_array($container->getParameter('kernel.environment'), ['dev', 'test'])) {
            $container->setParameter(
                'engineblock_url',
                sprintf('https://engine.%s', $container->getParameter('domain'))
            );
        }
    }

    /**
     * Loads the feature configuration in a manner that can be dumped in the container cache
     *
     * @param ContainerBuilder $container
     * @param array            $featureConfiguration
     */
    private function setFeatureConfiguration(ContainerBuilder $container, array $featureConfiguration)
    {
        // do note that duplicates cannot exist since the feature keys are keys in the configuration, which are
        // enforced to be unique by the config component.
        $features = [];
        foreach ($featureConfiguration as $feature => $onOrOff) {
            $features[$feature] = new Definition(Feature::class, [$feature, $onOrOff]);
        }

        $featureConfigurationService = $container->getDefinition('engineblock.features');
        $featureConfigurationService->replaceArgument(0, $features);
    }
}
