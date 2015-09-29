<?php

use EngineBlock_Log_Monolog_Processor_ProcessorFactory as ProcessorFactory;
use EngineBlock_Log_Monolog_Processor_RequestIdProcessor as RequestIdProcessor;

final class EngineBlock_Log_Monolog_Processor_RequestIdProcessorFactory implements ProcessorFactory
{
    public static function factory(array $config)
    {
        return new EngineBlock_Log_Monolog_Processor_RequestIdProcessor();
    }
}
