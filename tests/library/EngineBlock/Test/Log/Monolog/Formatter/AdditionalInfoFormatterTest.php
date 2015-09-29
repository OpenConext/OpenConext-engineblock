<?php

use Monolog\Formatter\FormatterInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

final class EngineBlock_Test_Log_Monolog_Formatter_AdditionalInfoFormatterTest extends PHPUnit_Framework_TestCase
{
    public function testItAddsAdditionalInfoWhenAnExceptionIsPresent()
    {
        $exception = new EngineBlock_Exception('message', EngineBlock_Exception::CODE_EMERGENCY);

        /** @var MockObject|FormatterInterface $decoratedFormatter */
        $decoratedFormatter = $this->getMockBuilder('Monolog\Formatter\FormatterInterface')->getMock();
        $decoratedFormatter->expects($this->once())
            ->method('format')
            ->with($this->callback(function ($record) {
                return is_array($record['context']['exception'])
                    && $record['context']['exception']['severity'] === 'EMERG';
            }))
            ->willReturnArgument(0);

        $formatter = new EngineBlock_Log_Monolog_Formatter_AdditionalInfoFormatter($decoratedFormatter);
        $formatter->format(array('context' => array('exception' => $exception)));
    }

    public function testItAddsAdditionalInfoInBatchWheneverAnExceptionIsPresent()
    {
        $exception = new EngineBlock_Exception('message');

        /** @var MockObject|FormatterInterface $decoratedFormatter */
        $decoratedFormatter = $this->getMockBuilder('Monolog\Formatter\FormatterInterface')->getMock();
        $decoratedFormatter->expects($this->once())
            ->method('formatBatch')
            ->with($this->callback(function ($records) {
                return is_array($records[0]['context']['exception'])
                    && $records[0]['context']['exception']['severity'] === 'ERROR';
            }))
            ->willReturnArgument(0);

        $formatter = new EngineBlock_Log_Monolog_Formatter_AdditionalInfoFormatter($decoratedFormatter);
        $formatter->formatBatch(array(array('context' => array('exception' => $exception))));
    }

    public function testItDoesntAdditionalInfoWhenARegularExceptionIsPresent()
    {
        $exception = new Exception('message');

        /** @var MockObject|FormatterInterface $decoratedFormatter */
        $decoratedFormatter = $this->getMockBuilder('Monolog\Formatter\FormatterInterface')->getMock();
        $decoratedFormatter->expects($this->once())
            ->method('format')
            ->with($this->callback(function ($record) use ($exception) {
                return $record['context']['exception'] === $exception;
            }))
            ->willReturnArgument(0);

        $formatter = new EngineBlock_Log_Monolog_Formatter_AdditionalInfoFormatter($decoratedFormatter);
        $formatter->format(array('context' => array('exception' => $exception)));
    }
}
