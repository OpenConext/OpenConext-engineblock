<?php

namespace OpenConext\EngineBlock\Logger\Processor;

use Mockery as m;
use OpenConext\EngineBlock\Request\RequestId;
use PHPUnit_Framework_TestCase as TestCase;

class RequestIdProcessorTest extends TestCase
{
    /**
     * @test
     * @group EngineBlock
     * @group Request
     * @group Logger
     */
    public function request_id_is_added_to_the_record()
    {
        $requestIdValue = 'some_request_id';

        $requestIdGenerator = m::mock('OpenConext\EngineBlock\Request\RequestIdGenerator');
        $requestIdGenerator->shouldReceive('generateRequestId')
            ->once()
            ->andReturn($requestIdValue);

        $requestId = new RequestId($requestIdGenerator);

        $requestIdProcessor = new RequestIdProcessor($requestId);
        $record = array('extra' => array());

        $processedRecord = $requestIdProcessor->processRecord($record);

        $this->assertEquals($requestIdValue, $processedRecord['extra']['request_id']);
    }
}
