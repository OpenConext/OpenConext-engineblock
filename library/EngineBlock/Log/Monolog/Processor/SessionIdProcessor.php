<?php

final class EngineBlock_Log_Monolog_Processor_SessionIdProcessor
{
    /**
     * @param array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        $record['extra']['session_id'] = session_id() ?: null;

        return $record;
    }
}
