<?php

use EngineBlock_Log_InvalidConfigurationException as InvalidConfigurationException;
use EngineBlock_Log_LogLevel as LogLevel;
use EngineBlock_Log_Monolog_Handler_HandlerFactory as HandlerFactory;
use Monolog\Handler\SyslogHandler;
use Psr\Log\LogLevel as PsrLogLevel;

/**
 * Creates a SyslogHandler.
 *
 * Example configuration:
 *
 *     factory = "EngineBlock_Log_Monolog_Handler_SyslogHandlerFactory"
 *     conf.ident = "EBLOG"
 *     conf.min_level ; OPTIONAL, defaults to "DEBUG"
 */
final class EngineBlock_Log_Monolog_Handler_SyslogHandlerFactory implements HandlerFactory
{
    public static function factory(array $config, array $handlers, $debug)
    {
        $config = self::validateAndNormaliseConfig($config);

        $handler = new SyslogHandler($config['ident'], LOG_USER, $debug ? PsrLogLevel::DEBUG : $config['min_level']);
        $handler->setFormatter($config['formatter']['factory']::factory($config['formatter']['conf']));

        return $handler;
    }

    /**
     * @param array $config
     * @return array
     * @throws InvalidConfigurationException
     */
    private static function validateAndNormaliseConfig(array $config)
    {
        if (!isset($config['ident'])) {
            throw InvalidConfigurationException::missing('ident', 'string');
        }

        if (!is_string($config['ident'])) {
            throw InvalidConfigurationException::invalidType('ident', $config['ident'], 'string');
        }

        if (!isset($config['min_level'])) {
            $config['min_level'] = PsrLogLevel::DEBUG;
        } elseif (!is_string($config['min_level'])) {
            throw InvalidConfigurationException::invalidType('min_level', $config['min_level'], 'string');
        }

        $config['min_level'] = strtolower($config['min_level']);

        if (!LogLevel::isValid($config['min_level'])) {
            throw new InvalidConfigurationException(
                sprintf("'min_level' is not a valid log level string, '%s' given", $config['min_level'])
            );
        }

        if (!isset($config['formatter']['factory'])) {
            throw InvalidConfigurationException::missing("formatter.factory", 'string');
        }

        if (!is_string($config['formatter']['factory'])) {
            throw InvalidConfigurationException::invalidType(
                "formatter.factory",
                $config['formatter']['factory'],
                'string'
            );
        }

        InvalidConfigurationException::assertIsValidFactory(
            $config['formatter']['factory'],
            'EngineBlock_Log_Monolog_Formatter_FormatterFactory'
        );

        if (!isset($config['formatter']['conf'])) {
            $config['formatter']['conf'] = array();
        } elseif (!is_array($config['formatter']['conf'])) {
            throw InvalidConfigurationException::invalidType(
                "formatter.conf",
                $config['formatter']['factory'],
                'array'
            );
        }

        return $config;
    }
}
