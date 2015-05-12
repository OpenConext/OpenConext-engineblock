<?php

use EngineBlock_Log_InvalidConfigurationException as InvalidConfigurationException;
use EngineBlock_Log_Monolog_Formatter_AdditionalInfoFormatter as AdditionalInfoFormatter;
use EngineBlock_Log_Monolog_Formatter_FormatterFactory as FormatterFactory;
use Monolog\Formatter\LineFormatter;

final class EngineBlock_Log_Monolog_Formatter_AdditionalInfoLineFormatterFactory implements FormatterFactory
{
    public static function factory(array $config)
    {
        $config = self::validateAndNormaliseConfig($config);

        return new AdditionalInfoFormatter(new LineFormatter($config['format']));
    }

    private static function validateAndNormaliseConfig(array $config)
    {
        if (!isset($config['format'])) {
            $config['format'] = null;
        } elseif (!is_string($config['format'])) {
            throw InvalidConfigurationException::invalidType('format', $config['format'], 'string');
        }

        return $config;
    }
}
