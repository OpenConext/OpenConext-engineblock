<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new OpenConext\EngineBlockBundle\OpenConextEngineBlockBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();

            // own bundles
            $bundles[] = new OpenConext\EngineBlockFunctionalTestingBundle\OpenConextEngineBlockFunctionalTestingBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }

    public function getCacheDir()
    {
        if ($this->environment === 'dev') {
            return '/tmp/engineblock/symfony-cache';
        }

        return $this->rootDir . '/cache/' . $this->environment;
    }

    public function getLogDir()
    {
        if ($this->environment === 'dev') {
            return '/var/log/engineblock';
        }

        return $this->rootDir . '/logs/' . $this->environment;
    }
}
