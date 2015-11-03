<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
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
            new OpenConext\EngineBlock\CompatibilityBundle\OpenConextEngineBlockCompatibilityBundle(),
            new OpenConext\EngineBlock\ApiBundle\OpenConextEngineBlockApiBundle(),
            new OpenConext\EngineBlock\AuthenticationBundle\OpenConextEngineBlockAuthenticationBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    /**
     * {@inheritDoc} We're overriding the default functionality here to be able to set the configured layout on
     * the application singleton. This because the view/layout system has to be configured to point to the correct
     * directories for rendering templates. Since not all templates are rendered in the controllers, but can be
     * rendered by the EngineBlock_Corto_ProxyServer we have to ensure that that uses the same configured view and
     * layout system.
     * This must be done every request. The only method that is guaranteed to execute for every request is this method.
     * However, we must run *after* the boot call, which ensures the container is built, since we need a service
     * from the container. This could also be located in the getHttpKernel method, however that would introduce a
     * side-effect to a getter method. Another option could be to configure the layout and view in the proyxserver,
     * however that makes refactoring in the future harder.
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if (false === $this->booted) {
            $this->boot();
        }

        // set the configured layout on the application singleton
        $this->engineBlockSingleton->setLayout($this->container->get('eb.compat.layout'));

        return $this->getHttpKernel()->handle($request, $type, $catch);
    }

    /**
     * Before building the container, the container is prepared. After the default preparations are done
     * we set some additional parameters with values defined in the ini configuration, as parsed by
     * the EngineBlock_ApplicationSingleton class. This allows for having parameters that are not configured
     * in the symfony configuration (yet).
     *
     * @param ContainerBuilder $container
     */
    protected function prepareContainer(ContainerBuilder $container)
    {
        parent::prepareContainer($container);
        $iniConfiguration = $this->engineBlockSingleton->getConfiguration();

        $container->setParameter('domain', $iniConfiguration->get('base_domain'));

        $trustedProxies = $iniConfiguration->get('trustedProxyIps', array());
        if (!is_array($trustedProxies)) {
            $trustedProxies = array();
        }
        $container->setParameter('trusted_proxies', $trustedProxies);

        $apiConfiguration = $iniConfiguration->get('engineApi');

        $features = $apiConfiguration->get('features');
        $container->setParameter('api.features.metadata_push.enabled', (bool) $features->get('metadataPush'));

        $apiJanusCredentials = $apiConfiguration->get('users')->get('janus');
        $container->setParameter('api.users.janus.username', $apiJanusCredentials->get('username'));
        $container->setParameter('api.users.janus.password', $apiJanusCredentials->get('password'));
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }

    public function getCacheDir()
    {
        return $this->engineBlockSingleton->getConfiguration()->get('symfony')->get('cachePath');
    }

    public function getLogDir()
    {
        return $this->engineBlockSingleton->getConfiguration()->get('symfony')->get('logPath');
    }
}
