<?php

namespace OpenConext\EngineBlock\CompatibilityBundle\Configuration;

use Symfony\Component\DependencyInjection\ContainerBuilder;

final class EngineBlockConfigurationLoader
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var EngineBlockIniFileLoader
     */
    private $fileLoader;

    public function __construct(ContainerBuilder $container, EngineBlockIniFileLoader $fileLoader)
    {
        $this->container  = $container;
        $this->fileLoader = $fileLoader;
    }

    /**
     * @param string[] $filePaths
     * @return EngineBlockConfiguration
     */
    public function loadFiles(array $filePaths)
    {
        return $this->fileLoader->load($filePaths);
    }
}
