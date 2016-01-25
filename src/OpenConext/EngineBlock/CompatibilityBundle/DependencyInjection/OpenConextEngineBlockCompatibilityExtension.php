<?php

namespace OpenConext\EngineBlock\CompatibilityBundle\DependencyInjection;

use EngineBlock_Application_Bootstrapper;
use OpenConext\EngineBlock\CompatibilityBundle\Configuration\EngineBlockIniFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class OpenConextEngineBlockCompatibilityExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('event_listeners.yml');

        $engineBlockLoader = new EngineBlockIniFileLoader;
        $engineBlockConfig = $engineBlockLoader->load(array(
            $container->getParameter('kernel.root_dir') . EngineBlock_Application_Bootstrapper::CONFIG_FILE_DEFAULT,
            EngineBlock_Application_Bootstrapper::CONFIG_FILE_ENVIRONMENT,
        ));

        $container
            ->getDefinition('eb.compat.engineblock_config')
            ->replaceArgument(0, $engineBlockConfig);
    }
}
