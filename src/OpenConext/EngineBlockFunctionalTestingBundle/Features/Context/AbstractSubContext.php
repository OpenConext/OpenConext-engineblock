<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Class AbstractSubContext
 */
abstract class AbstractSubContext implements Context
{
    protected $minkContext;
    protected $mockIdpContext;
    protected $mockSpContext;
    protected $engineBlockContext;

    /**
     * @BeforeScenario
     */
    public function prepareContext(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        $this->minkContext = $environment->getContext(MinkContext::class);
        $this->mockIdpContext = $environment->getContext(MockIdpContext::class);
        $this->mockSpContext = $environment->getContext(MockSpContext::class);
        $this->engineBlockContext = $environment->getContext(EngineBlockContext::class);
    }

    public function getMinkContext()
    {
        return $this->minkContext;
    }
}
