<?php

namespace OpenConext\EngineBlock\Logger\Processor;

final class SessionIdProcessor
{
    /**
     * @param array $record
     * @return array
     */
    public function processRecord(array $record)
    {
        $record['extra']['session_id'] = session_id() ?: null;

        return $record;
    }
}
