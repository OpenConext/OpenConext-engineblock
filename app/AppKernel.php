<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        $bundles = [
            // Core Symfony
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),

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
            $bundles[] = new FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $configurationDirectory = $this->getProjectDir() . '/app/config/';
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

    public function getCacheDir(): string
    {
        // In the dev & test environments use a folder outside the shared filesystem. This greatly improves cache clear
        // and warmup time.
        if ($this->getEnvironment() === 'dev' || $this->getEnvironment() === 'test') {
            return sprintf('/tmp/engineblock/cache/%s', $this->getEnvironment());
        }

        return $this->getProjectDir() . '/app/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/app/logs/' . $this->environment;
    }
}
