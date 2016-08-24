<?php

namespace OpenConext\EngineBlock\Logger\Handler\FingersCrossed;

use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;

interface ActivationStrategyFactory
{
    /**
     * @param array $config
     * @return ActivationStrategyInterface
     */
    public static function createActivationStrategy(array $config);
}
