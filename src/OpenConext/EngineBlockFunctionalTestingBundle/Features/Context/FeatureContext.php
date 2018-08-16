<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Behat\Context\Context;

/**
 * Class FeatureContext
 */
class FeatureContext implements Context
{
    const SUB_CONTEXT_MINK          = 'mink';
    const SUB_CONTEXT_ENGINE_BLOCK  = 'engine';
    const SUB_CONTEXT_MOCK_IDP      = 'idp';
    const SUB_CONTEXT_MOCK_SP       = 'sp';

    const PARAM_NAME_ENGINE_URL         = 'engineblock_url';
    const PARAM_NAME_ETS_URL            = 'engine_test_stand_url';
    const PARAM_NAME_IDP_FIXTURE_FILE   = 'idp_fixture_file';
    const PARAM_NAME_SP_FIXTURE_FILE    = 'sp_fixture_file';

    protected $containerParameters = [
        self::PARAM_NAME_ENGINE_URL,
        self::PARAM_NAME_ETS_URL,
        self::PARAM_NAME_IDP_FIXTURE_FILE,
        self::PARAM_NAME_SP_FIXTURE_FILE,
    ];

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var \Symfony\Component\HttpFoundation\ParameterBag
     */
    private $parameters;

    /**
     * @var Container
     */
    private static $container;

    /**
     * Initializes context with parameters from behat.yml.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = new ParameterBag($parameters);
    }

    /**
     * @return ParameterBag
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Sets HttpKernel instance.
     * This method will be automatically called by Symfony2Extension ContextInitializer.
     *
     * @param KernelInterface $kernel
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;

        if ($this->kernel->getContainer() && !self::$container) {
            self::$container = $this->kernel->getContainer();
        }

        $this->initSubContexts();
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario()
    {
        $this->initSubContexts();
    }

    /**
     * The contexts that actually hold the step definitions.
     */
    protected function initSubContexts()
    {
        $container = self::$container;

        if (!$container) {
            throw new \RuntimeException('No container!');
        }

        $this->useContext(
            self::SUB_CONTEXT_MINK,
            $container->get('engineblock.functional_testing.behat_context.mink')
        );

        $this->useContext(
            self::SUB_CONTEXT_ENGINE_BLOCK,
            $container->get('engineblock.functional_testing.behat_context.engine_block')
        );

        $this->useContext(
            self::SUB_CONTEXT_MOCK_IDP,
            $container->get('engineblock.functional_testing.behat_context.mock_idp')
        );

        $this->useContext(
            self::SUB_CONTEXT_MOCK_SP,
            $container->get('engineblock.functional_testing.behat_context.mock_sp')
        );
    }

    /**
     * @return MinkContext
     */
    public function getMinkContext()
    {
        return $this->getSubcontext(self::SUB_CONTEXT_MINK);
    }

    /**
     * @return string
     */
    public function getPageContent()
    {
        return $this->getMinkContext()->getSession()->getPage()->getContent();
    }
}
