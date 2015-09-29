<?php

use Monolog\Logger;

final class EngineBlock_Test_Log_Monolog_Handler_SyslogFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testItCreatesASyslogHandler()
    {
        $config = array(
            'ident'     => 'qwerty',
            'formatter' => array('factory' => 'EngineBlock_Test_Log_Monolog_Formatter_FormatterStubFactory'),
        );

        $formatter = $this->getMockBuilder('Monolog\Formatter\FormatterInterface')->getMock();
        EngineBlock_Test_Log_Monolog_Formatter_FormatterStubFactory::$stubToReturn = $formatter;

        $handler = EngineBlock_Log_Monolog_Handler_SyslogHandlerFactory::factory($config, array(), false);

        $this->assertInstanceOf('Monolog\Handler\SyslogHandler', $handler);
    }

    public function testItDefaultsToAMinimumLevelOfDebug()
    {
        $config = array(
            'ident'     => 'qwerty',
            'formatter' => array('factory' => 'EngineBlock_Test_Log_Monolog_Formatter_FormatterStubFactory'),
        );

        $formatter = $this->getMockBuilder('Monolog\Formatter\FormatterInterface')->getMock();
        EngineBlock_Test_Log_Monolog_Formatter_FormatterStubFactory::$stubToReturn = $formatter;

        $handler = EngineBlock_Log_Monolog_Handler_SyslogHandlerFactory::factory($config, array(), false);
        $this->assertTrue($handler->isHandling(array('level' => Logger::DEBUG)));

        $this->assertInstanceOf('Monolog\Handler\SyslogHandler', $handler);
    }

    public function testItAcceptsAMinimumLevel()
    {
        $config = array(
            'ident'     => 'qwerty',
            'formatter' => array('factory' => 'EngineBlock_Test_Log_Monolog_Formatter_FormatterStubFactory'),
            'min_level' => 'INFO',
        );

        $formatter = $this->getMockBuilder('Monolog\Formatter\FormatterInterface')->getMock();
        EngineBlock_Test_Log_Monolog_Formatter_FormatterStubFactory::$stubToReturn = $formatter;

        $handler = EngineBlock_Log_Monolog_Handler_SyslogHandlerFactory::factory($config, array(), false);
        $this->assertFalse($handler->isHandling(array('level' => Logger::DEBUG)));
        $this->assertTrue($handler->isHandling(array('level' => Logger::INFO)));

        $this->assertInstanceOf('Monolog\Handler\SyslogHandler', $handler);
    }

    public function testItSetsMinimumLevelToDebugWhenDebugIsTrue()
    {
        $config = array(
            'ident'     => 'qwerty',
            'formatter' => array('factory' => 'EngineBlock_Test_Log_Monolog_Formatter_FormatterStubFactory'),
            'min_level' => 'INFO',
        );

        $formatter = $this->getMockBuilder('Monolog\Formatter\FormatterInterface')->getMock();
        EngineBlock_Test_Log_Monolog_Formatter_FormatterStubFactory::$stubToReturn = $formatter;

        $handler = EngineBlock_Log_Monolog_Handler_SyslogHandlerFactory::factory($config, array(), true);
        $this->assertTrue($handler->isHandling(array('level' => Logger::DEBUG)));

        $this->assertInstanceOf('Monolog\Handler\SyslogHandler', $handler);
    }
    public function configurationTests()
    {
        return array(
            'no ident' => array(
                array(),
                'expected configuration key "ident" containing a string'
            ),
            'no formatter' => array(
                array('ident' => 'qwerty'),
                'expected configuration key "formatter.factory" containing a string'
            ),
            'invalid min level' => array(
                array('ident' => 'qwerty', 'formatter' => array('factory' => 'stdClass'), 'min_level' => 'INVALID'),
                "'min_level' is not a valid log level string"
            ),
        );
    }

    /**
     * @dataProvider configurationTests
     * @param array  $config
     * @param string $expectedExceptionMessageContains
     */
    public function testItValidatesConfiguration(array $config, $expectedExceptionMessageContains)
    {
        $this->setExpectedException('EngineBlock_Log_InvalidConfigurationException', $expectedExceptionMessageContains);

        EngineBlock_Log_Monolog_Handler_SyslogHandlerFactory::factory($config, array(), false);
    }
}
