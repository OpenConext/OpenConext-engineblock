<?php

final class EngineBlock_Test_Log_Monolog_Processor_RequestIdProcessorFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testItAddsANonEmptyStringToTheRecord()
    {
        $processor = EngineBlock_Log_Monolog_Processor_RequestIdProcessorFactory::factory(array());

        $this->assertInstanceOf('EngineBlock_Log_Monolog_Processor_RequestIdProcessor', $processor);
    }
}
