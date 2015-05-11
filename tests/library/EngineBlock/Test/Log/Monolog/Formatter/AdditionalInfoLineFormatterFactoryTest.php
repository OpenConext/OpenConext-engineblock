<?php

final class EngineBlock_Test_Log_Monolog_Formatter_AdditionalInfoLineFormatterFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testItIsCreated()
    {
        $formatter = EngineBlock_Log_Monolog_Formatter_AdditionalInfoLineFormatterFactory::factory(array());

        $this->assertInstanceOf('EngineBlock_Log_Monolog_Formatter_AdditionalInfoFormatter', $formatter);
    }
}
