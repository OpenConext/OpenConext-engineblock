<?php

final class EngineBlock_Test_Log_Monolog_Processor_RequestIdProcessorTest extends PHPUnit_Framework_TestCase
{
    public function testItAddsANonEmptyStringToTheRecord()
    {
        $processor = EngineBlock_Log_Monolog_Processor_RequestIdProcessor::fromUniqid();
        $record = $processor(array('extra' => array()));

        $this->assertInternalType('string', $record['extra']['request_id'], 'Appended request ID must be a string');
        $this->assertGreaterThan(0, strlen($record['extra']['request_id']), 'Appended request ID may not be empty');
    }
}
