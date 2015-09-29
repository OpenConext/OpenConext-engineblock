<?php

final class EngineBlock_Test_Log_Monolog_Processor_SessionIdProcessorFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testItAddsANonEmptyStringToTheRecord()
    {
        $processor = EngineBlock_Log_Monolog_Processor_SessionIdProcessorFactory::factory(array());

        $this->assertInstanceOf('EngineBlock_Log_Monolog_Processor_SessionIdProcessor', $processor);
    }
}
