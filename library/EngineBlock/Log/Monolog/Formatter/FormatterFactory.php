<?php
use Monolog\Formatter\FormatterInterface;

interface EngineBlock_Log_Monolog_Formatter_FormatterFactory
{
    /**
     * @param array $config
     * @return FormatterInterface
     */
    public static function factory(array $config);
}
