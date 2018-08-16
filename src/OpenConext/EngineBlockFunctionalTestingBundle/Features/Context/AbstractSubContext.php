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

    /**
     * @BeforeScenario
     */
    public function prepareContext(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $this->minkContext = $environment->getContext(MinkContext::class);
    }

    public function getMinkContext()
    {
        return $this->minkContext;
    }
}
