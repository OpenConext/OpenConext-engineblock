<?php

interface EngineBlock_Log_Monolog_Processor_ProcessorFactory
{
    /**
     * @param array $config
     * @return callable
     */
    public static function factory(array $config);
}
