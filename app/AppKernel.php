<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    private $engineBlockSingleton;

    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);

        $this->engineBlockSingleton = EngineBlock_ApplicationSingleton::getInstance();
        $this->engineBlockSingleton->bootstrap();
    }

    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new OpenConext\EngineBlock\ApiBundle\OpenConextEngineBlockApiBundle(),
            new OpenConext\EngineBlock\AuthenticationBundle\OpenConextEngineBlockAuthenticationBundle(),
            new OpenConext\EngineBlock\ProfileBundle\OpenConextEngineBlockProfileBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }

    public function getCacheDir()
    {
        return '/var/cache/engineblock';
    }

    public function getLogDir()
    {
        return '/var/log/engineblock';
    }
}
