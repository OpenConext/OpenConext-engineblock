<?php

final class EngineBlock_Test_Log_Monolog_Formatter_AdditionalInfoLineFormatterFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testItIsCreated()
    {
        $formatter = EngineBlock_Log_Monolog_Formatter_AdditionalInfoLineFormatterFactory::factory(array());

        $this->assertInstanceOf('EngineBlock_Log_Monolog_Formatter_AdditionalInfoFormatter', $formatter);
    }

    public function testItsFormatCanBeConfigured()
    {
        $config = array('format' => 'The same every time');
        $formatter = EngineBlock_Log_Monolog_Formatter_AdditionalInfoLineFormatterFactory::factory($config);
        $formatted = $formatter->format(array('message' => 'Where did my message go?', 'extra' => array()));

        $this->assertSame('The same every time', $formatted);
    }
}
