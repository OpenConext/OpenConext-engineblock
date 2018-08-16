<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use Behat\Behat\Context\Context;
/**
 * Class AbstractSubContext
 */
abstract class AbstractSubContext implements Context
{
    /**
     * @BeforeScenario
     * @return FeatureContext
     */
    public function getMainContext(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        return $environment->getContext(MinkContext::class);
    }
}
