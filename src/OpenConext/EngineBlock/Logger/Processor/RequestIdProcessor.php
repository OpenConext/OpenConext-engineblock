<?php

namespace OpenConext\EngineBlock\Logger\Processor;

use OpenConext\EngineBlock\Request\RequestId;

final class RequestIdProcessor
{
    /**
     * @var RequestId
     */
    private $requestId;

    public function __construct(RequestId $requestId)
    {
        $this->requestId = $requestId;
    }

    /**
     * @param array $record
     * @return array
     */
    public function processRecord(array $record)
    {
        $record['extra']['request_id'] = $this->requestId->get();

        return $record;
    }
}
