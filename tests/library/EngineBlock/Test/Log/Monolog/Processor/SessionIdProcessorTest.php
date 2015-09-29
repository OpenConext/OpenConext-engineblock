<?php

final class EngineBlock_Test_Log_Monolog_Processor_SessionIdProcessorTest extends PHPUnit_Framework_TestCase
{
    public function testItAddsTheSessionIdToTheRecordWhenSessionIsStarted()
    {
        $processor = new EngineBlock_Log_Monolog_Processor_SessionIdProcessor();

        session_id('arthur42');
        $record = $processor(array('extra' => array()));

        $this->assertSame('arthur42', $record['extra']['session_id']);
    }

    public function testItAddsNullTheRecordWhenSessionIsNotStarted()
    {
        $processor = new EngineBlock_Log_Monolog_Processor_SessionIdProcessor();

        session_id(null);
        $record = $processor(array('extra' => array()));

        $this->assertSame(null, $record['extra']['session_id']);
    }
}
