<?php

use EngineBlock_Log_Monolog_Formatter_AdditionalInfoFormatter as AdditionalInfoFormatter;
use EngineBlock_Log_Monolog_Formatter_FormatterFactory as FormatterFactory;
use Monolog\Formatter\LineFormatter;

final class EngineBlock_Log_Monolog_Formatter_AdditionalInfoLineFormatterFactory implements FormatterFactory
{
    public static function factory(array $config)
    {
        return new AdditionalInfoFormatter(new LineFormatter());
    }
}
