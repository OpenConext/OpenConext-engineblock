<?php

use EngineBlock_Log_InvalidConfigurationException as InvalidConfigurationException;
use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;

interface EngineBlock_Log_Monolog_Handler_FingersCrossed_ActivationStrategyFactory
{
    /**
     * @param array $config
     * @return ActivationStrategyInterface
     * @throws InvalidConfigurationException
     */
    public static function factory(array $config);
}
