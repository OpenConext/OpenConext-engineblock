<?php

use EngineBlock_Log_Monolog_Handler_HandlerFactory as HandlerFactory;
use EngineBlock_Log_InvalidConfigurationException as InvalidConfigurationException;
use EngineBlock_Log_LogLevel as LogLevel;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Logger;

/**
 * Creates a FingersCrossedHandler.
 *
 * Example configuration:
 *
 *     factory = "EngineBlock_Log_Monolog_Handler_FingersCrossedHandlerFactory"
 *     conf.handler = "syslog" ; Delegate handler
 *     conf.passthru_level = "NOTICE" ; OPTIONAL, disabled by default
 *     conf.activation_strategy.factory = "..."
 */
final class EngineBlock_Log_Monolog_Handler_FingersCrossedHandlerFactory implements HandlerFactory
{
    public static function factory(array $config, array $handlers, $debug)
    {
        $config = self::validateAndNormaliseConfig($config);

        if (!isset($handlers[$config['handler']])) {
            throw new InvalidConfigurationException(
                sprintf("No handler with name '%s' defined. Did you define it before this handler?", $config['handler'])
            );
        }

        if ($debug) {
            // When debug is enabled, do not buffer messages, but let them be handled our delegate handler.
            return $handlers[$config['handler']];
        }

        return new FingersCrossedHandler(
            $handlers[$config['handler']],
            $config['activation_strategy']['factory']::factory($config['activation_strategy']['conf']),
            0,
            true,
            true,
            Logger::toMonologLevel($config['passthru_level'])
        );
    }

    /**
     * @param array $config
     * @return array
     * @throws InvalidConfigurationException
     */
    private static function validateAndNormaliseConfig(array $config)
    {
        if (!isset($config['handler'])) {
            throw InvalidConfigurationException::missing('handler', 'string');
        }

        if (!is_string($config['handler'])) {
            throw InvalidConfigurationException::invalidType('handler', $config['ident'], 'string');
        }

        if (!isset($config['passthru_level'])) {
            $config['passthru_level'] = null;
        } elseif (!is_string($config['passthru_level'])) {
            throw InvalidConfigurationException::invalidType('passthru_level', $config['passthru_level'], 'string');
        } else {
            $config['passthru_level'] = strtolower($config['passthru_level']);

            if (!LogLevel::isValid($config['passthru_level'])) {
                throw new InvalidConfigurationException(
                    sprintf("'passthru_level' is not a valid log level string, '%s' given", $config['passthru_level'])
                );
            }
        }

        if (!isset($config['activation_strategy']['factory'])) {
            throw InvalidConfigurationException::missing("activation_strategy.factory", 'string');
        }

        if (!is_string($config['activation_strategy']['factory'])) {
            throw InvalidConfigurationException::invalidType(
                "activation_strategy.factory",
                $config['activation_strategy']['factory'],
                'string'
            );
        }

        InvalidConfigurationException::assertIsValidFactory(
            $config['activation_strategy']['factory'],
            'EngineBlock_Log_Monolog_Handler_FingersCrossed_ActivationStrategyFactory'
        );

        if (!isset($config['activation_strategy']['conf'])) {
            $config['activation_strategy']['conf'] = array();
        } elseif (!is_array($config['activation_strategy']['conf'])) {
            throw InvalidConfigurationException::invalidType(
                "activation_strategy.conf",
                $config['activation_strategy']['factory'],
                'array'
            );
        }

        return $config;
    }
}
