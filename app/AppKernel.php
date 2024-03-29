<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // Core Symfony
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),

            // Sensio helpers
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle(),

            // Doctrine integration
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),

            // OpenConext Monitor integration
            new OpenConext\MonitorBundle\OpenConextMonitorBundle(),

            // EngineBlock integration
            new OpenConext\EngineBlockBundle\OpenConextEngineBlockBundle(),
        ];

        if (in_array($this->getEnvironment(), array('dev', 'test', 'ci'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();

            // own bundles
            $bundles[] = new OpenConext\EngineBlockFunctionalTestingBundle\OpenConextEngineBlockFunctionalTestingBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $configurationDirectory = $this->getRootDir() . '/config/';
        $loader->load($configurationDirectory . 'config_' . $this->getEnvironment() . '.yml');

        $localConfiguration = $configurationDirectory . 'config_local.yml';
        if (!file_exists($localConfiguration)) {
            return;
        }

        if (!is_readable($localConfiguration)) {
            throw new \RuntimeException(sprintf('Local configuration file "%s" is not readable', $localConfiguration));
        }

        $loader->load(($localConfiguration));
    }

    public function getCacheDir()
    {
        // In the dev & test environments use a folder outside the shared filesystem. This greatly improves cache clear
        // and warmup time.
        if ($this->getEnvironment() === 'dev' || $this->getEnvironment() === 'test') {
            return sprintf('/tmp/engineblock/cache/%s', $this->getEnvironment());
        }

        return $this->rootDir . '/cache/' . $this->environment;
    }

    public function getLogDir()
    {
        return $this->rootDir . '/logs/' . $this->environment;
    }
}
