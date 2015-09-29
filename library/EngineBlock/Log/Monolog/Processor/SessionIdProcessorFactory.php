<?php

use EngineBlock_Log_Monolog_Processor_ProcessorFactory as ProcessorFactory;
use EngineBlock_Log_Monolog_Processor_SessionIdProcessor as SessionIdProcessor;

final class EngineBlock_Log_Monolog_Processor_SessionIdProcessorFactory implements ProcessorFactory
{
    public static function factory(array $config)
    {
        return new SessionIdProcessor();
    }
}
