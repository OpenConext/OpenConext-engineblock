<?php

final class EngineBlock_Log_Monolog_Processor_RequestIdProcessor
{
    /**
     * @var string
     */
    private $requestId;

    /**
     * @return EngineBlock_Log_Monolog_Processor_RequestIdProcessor
     */
    public static function fromUniqid()
    {
        $processor = new self;
        $processor->requestId = uniqid();

        return $processor;
    }

    private function __construct()
    {
    }

    /**
     * @param array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        $record['extra']['request_id'] = $this->requestId;

        return $record;
    }
}
