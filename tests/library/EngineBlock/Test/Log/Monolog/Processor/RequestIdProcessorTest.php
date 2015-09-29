<?php

final class EngineBlock_Test_Log_Monolog_Processor_RequestIdProcessorTest extends PHPUnit_Framework_TestCase
{
    public function testItAddsANonEmptyStringToTheRecord()
    {
        // Assert the log ID is bootstrapped.
        $logId = EngineBlock_ApplicationSingleton::getInstance()->getLogRequestId();
        $this->assertInternalType('string', $logId);
        $this->assertNotEmpty($logId);

        $processor = new EngineBlock_Log_Monolog_Processor_RequestIdProcessor();
        $record = $processor(array('extra' => array()));

        $this->assertEquals(
            $logId,
            $record['extra']['request_id'],
            'Appended log request ID and bootstrapped log request ID do not match'
        );
    }
}
