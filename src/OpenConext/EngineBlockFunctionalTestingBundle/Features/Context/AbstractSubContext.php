<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Class AbstractSubContext
 */
abstract class AbstractSubContext implements Context
{
    /**
     * @var MinkContext
     */
    protected $minkContext;

    const SESSION_DEFAULT = 'default';
    const SESSION_CHROME = 'chrome';

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
