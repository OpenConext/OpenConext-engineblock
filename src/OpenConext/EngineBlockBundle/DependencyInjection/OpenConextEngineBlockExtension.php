<?php

namespace OpenConext\EngineBlockBundle\DependencyInjection;

use EngineBlock_Application_Bootstrapper;
use OpenConext\EngineBlockBridge\Configuration\EngineBlockIniFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class OpenConextEngineBlockExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('controllers.yml');
        $loader->load('event_listeners.yml');
        $loader->load('repositories.yml');
        $loader->load('services.yml');
        $loader->load('bridge.yml');
        $loader->load('compat.yml');

        $this->parseIniConfigurationFiles($container);
        $this->overwriteDefaultLogger($container);
        $this->setUrlParameterBasedOnEnv($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function parseIniConfigurationFiles(ContainerBuilder $container)
    {
        $engineBlockLoader = new EngineBlockIniFileLoader;
        $engineBlockConfig = $engineBlockLoader->load(
            array(
                $container->getParameter('kernel.root_dir') .
                '/../application/' . EngineBlock_Application_Bootstrapper::CONFIG_FILE_DEFAULT,
                EngineBlock_Application_Bootstrapper::CONFIG_FILE_ENVIRONMENT,
            )
        );

        $container
            ->getDefinition('engineblock.bridge.config')
            ->replaceArgument(0, $engineBlockConfig);
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
        if (in_array($container->getParameter('kernel.environment'), array('dev', 'test'))) {
            $container->setParameter(
                'engineblock_url',
                sprintf('https://engine.%s', $container->getParameter('domain'))
            );
        }
    }
}
