<?php

use EngineBlock_Log_InvalidConfigurationException as InvalidConfigurationException;
use Monolog\Logger;

/**
 * Creates a Logger.
 *
 * Example configuration:
 *
 *     factory = "EngineBlock_Log_Logger_LoggerFactory"
 *     conf.name = "engineblock"
 *     conf.handlers = "handler1,handler2"
 *     conf.handler1.factory = "..."
 *     conf.handler2.factory = "..."
 */
final class EngineBlock_Log_MonologLoggerFactory implements EngineBlock_Log_LoggerFactory
{
    public static function factory(array $config, $debug)
    {
        $config = self::validateAndNormaliseConfig($config);

        $allHandlers = array();
        foreach ($config['handler'] as $handlerName => $handlerConfig) {
            $allHandlers[$handlerName] = $handlerConfig['factory']::factory(
                $handlerConfig['conf'],
                $allHandlers,
                $debug
            );
        }

        $handlerNames = array_filter(array_map('trim', explode(',', $config['handlers'])));
        $handlers = array_map(
            function ($name) use ($allHandlers) {
                if (!isset($allHandlers[$name])) {
                    throw new InvalidConfigurationException(
                        sprintf("Logger handler '%s' has not been configured", $name)
                    );
                }

                return $allHandlers[$name];
            },
            $handlerNames
        );

        return new Logger($config['name'], $handlers);
    }

    /**
     * @param array $config
     * @return array
     * @throws InvalidConfigurationException
     */
    private static function validateAndNormaliseConfig(array $config)
    {
        if (!isset($config['name'])) {
            throw InvalidConfigurationException::missing('name', 'string');
        } elseif (!is_string($config['name'])) {
            throw InvalidConfigurationException::invalidType('name', $config['name'], 'string');
        }

        if (!isset($config['handlers'])) {
            throw InvalidConfigurationException::missing('handlers', 'string');
        } elseif (!is_string($config['handlers'])) {
            throw InvalidConfigurationException::invalidType(
                'handlers',
                $config['handlers'],
                'comma-separated string'
            );
        }

        if (!isset($config['handler'])) {
            $config['handler'] = array();
        } elseif (!is_array($config['handler'])) {
            throw InvalidConfigurationException::invalidType(
                'handler',
                $config['handler'],
                'array of handler configurations'
            );
        }

        foreach ($config['handler'] as $name => $handlerConfig) {
            $config['handler'][$name] = self::validateAndNormaliseHandlerConfig($name, $handlerConfig);
        }

        return $config;
    }

    /**
     * @param string $name
     * @param mixed  $handlerConfig
     * @return array
     * @throws InvalidConfigurationException
     */
    private static function validateAndNormaliseHandlerConfig($name, $handlerConfig)
    {
        if (!is_array($handlerConfig)) {
            throw InvalidConfigurationException::invalidType("handler.$name", $handlerConfig, 'array');
        }

        if (!isset($handlerConfig['factory'])) {
            throw InvalidConfigurationException::missing("handler.$name.factory", 'string');
        } elseif (!is_string($handlerConfig['factory'])) {
            throw InvalidConfigurationException::invalidType(
                "handler.$name.factory",
                $handlerConfig['factory'],
                'string'
            );
        }

        if (!isset($handlerConfig['conf'])) {
            $handlerConfig = array();
        } elseif (!is_array($handlerConfig['conf'])) {
            throw InvalidConfigurationException::invalidType("handler.$name.conf", $handlerConfig['factory'], 'array');
        }

        return $handlerConfig;
    }
}
