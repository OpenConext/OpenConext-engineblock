<?php

use EngineBlock_Log_Monolog_Handler_FingersCrossed_ActivationStrategyFactory as ActivationStrategyFactory;
use EngineBlock_Log_Monolog_Handler_FingersCrossed_ManualOrDecoratedActivationStrategy as ManualOrDecoratedActivationStrategy;
use EngineBlock_Log_InvalidConfigurationException as InvalidConfigurationException;
use EngineBlock_Log_LogLevel as LogLevel;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;

final class EngineBlock_Log_Monolog_Handler_FingersCrossed_ManualOrErrorLevelActivationStrategyFactory implements
    ActivationStrategyFactory
{
    /**
     * @var ManualOrDecoratedActivationStrategy|null
     */
    private static $strategy;

    /**
     * @param array $config
     * @return ManualOrDecoratedActivationStrategy
     * @throws InvalidConfigurationException
     */
    public static function factory(array $config)
    {
        if (isset(self::$strategy)) {
            throw new RuntimeException(
                "Cannot manufacture a second instance of this strategy, as the current instance is required for " .
                "explicit flushing of the log message buffer"
            );
        }

        $config = self::validateAndNormaliseConfig($config);

        self::$strategy = new ManualOrDecoratedActivationStrategy(
            new ErrorLevelActivationStrategy($config['action_level'])
        );

        return self::$strategy;
    }

    /**
     * @return ManualOrDecoratedActivationStrategy|null
     */
    public static function getManufacturedStrategy()
    {
        return self::$strategy;
    }

    /**
     * @param array $config
     * @return array
     * @throws InvalidConfigurationException
     */
    private static function validateAndNormaliseConfig(array $config)
    {
        if (!isset($config['action_level'])) {
            throw InvalidConfigurationException::missing('action_level', 'string');
        }

        if (!is_string($config['action_level'])) {
            throw InvalidConfigurationException::invalidType('action_level', $config['action_level'], 'string');
        }

        $config['action_level'] = strtolower($config['action_level']);

        if (!LogLevel::isValid($config['action_level'])) {
            throw new InvalidConfigurationException(
                sprintf("'action_level' is not a valid log level string, '%s' given", $config['action_level'])
            );
        }

        return $config;
    }
}
