<?php

final class EngineBlock_Log_Monolog_Processor_RequestIdProcessor
{
    /**
     * @param array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        $record['extra']['request_id'] = EngineBlock_ApplicationSingleton::getInstance()->getLogRequestId();

        return $record;
    }
}
