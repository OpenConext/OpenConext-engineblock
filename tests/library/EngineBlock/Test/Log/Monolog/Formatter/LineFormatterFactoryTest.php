<?php

final class EngineBlock_Test_Log_Monolog_Formatter_LineFormatterFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testItAddsANonEmptyStringToTheRecord()
    {
        $formatter = EngineBlock_Log_Monolog_Formatter_LineFormatterFactory::factory(array());

        $this->assertInstanceOf('Monolog\Formatter\LineFormatter', $formatter);
    }
}
