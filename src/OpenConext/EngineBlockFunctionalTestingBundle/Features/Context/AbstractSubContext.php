<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Features\Context;

use Behat\Behat\Context\BehatContext;

/**
 * Class AbstractSubContext
 */
abstract class AbstractSubContext extends BehatContext
{
    /**
     * @return FeatureContext
     */
    public function getMainContext()
    {
        return parent::getMainContext();
    }
}
