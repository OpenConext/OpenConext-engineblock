<?php

use Monolog\Formatter\LineFormatter;

/**
 * Creates a LineFormatter. No configuration options are currently available.
 */
final class EngineBlock_Log_Monolog_Formatter_LineFormatterFactory implements EngineBlock_Log_Monolog_Formatter_FormatterFactory
{
    public static function factory(array $config)
    {
        return new LineFormatter();
    }
}
