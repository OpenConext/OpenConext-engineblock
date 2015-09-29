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

        $processors = array_map(
            function (array $config) {
                return $config['factory']::factory($config['conf']);
            },
            $config['processor']
        );

        return new Logger($config['name'], $handlers, $processors);
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
        }

        if (!is_string($config['name'])) {
            throw InvalidConfigurationException::invalidType('name', $config['name'], 'string');
        }

        if (!isset($config['handlers'])) {
            throw InvalidConfigurationException::missing('handlers', 'string');
        }

        if (!is_string($config['handlers'])) {
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
            $config['handler'][$name] = self::validateAndNormaliseSubTypeConfig('handler', $name, $handlerConfig);
            InvalidConfigurationException::assertIsValidFactory(
                $config['handler'][$name]['factory'],
                'EngineBlock_Log_Monolog_Handler_HandlerFactory'
            );
        }

        if (!isset($config['processor'])) {
            $config['processor'] = array();
        } elseif (!is_array($config['processor'])) {
            throw InvalidConfigurationException::invalidType(
                'processor',
                $config['processor'],
                'array of processor configurations'
            );
        }

        foreach ($config['processor'] as $name => $processorConfig) {
            $config['processor'][$name] = self::validateAndNormaliseSubTypeConfig('processor', $name, $processorConfig);
            InvalidConfigurationException::assertIsValidFactory(
                $config['processor'][$name]['factory'],
                'EngineBlock_Log_Monolog_Processor_ProcessorFactory'
            );
        }

        return $config;
    }

    /**
     * @param string $typeKey
     * @param string $name
     * @param mixed  $config
     * @return array
     * @throws EngineBlock_Log_InvalidConfigurationException
     */
    private static function validateAndNormaliseSubTypeConfig($typeKey, $name, $config)
    {
        if (!is_array($config)) {
            throw InvalidConfigurationException::invalidType("$typeKey.$name", $config, 'array');
        }

        if (!isset($config['factory'])) {
            throw InvalidConfigurationException::missing("$typeKey.$name.factory", 'string');
        } elseif (!is_string($config['factory'])) {
            throw InvalidConfigurationException::invalidType(
                "$typeKey.$name.factory",
                $config['factory'],
                'string'
            );
        }

        if (!isset($config['conf'])) {
            $config['conf'] = array();
        } elseif (!is_array($config['conf'])) {
            throw InvalidConfigurationException::invalidType("$typeKey.$name.conf", $config['factory'], 'array');
        }

        return $config;
    }
}
