<?php

use Monolog\Formatter\FormatterInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

final class EngineBlock_Test_Log_Monolog_Formatter_AdditionalInfoFormatterTest extends PHPUnit_Framework_TestCase
{
    public function testItAddsAdditionalInfoWhenAnExceptionIsPresent()
    {
        $exception = new EngineBlock_Exception('message');

        /** @var MockObject|FormatterInterface $decoratedFormatter */
        $decoratedFormatter = $this->getMockBuilder('Monolog\Formatter\FormatterInterface')->getMock();
        $decoratedFormatter->expects($this->once())
            ->method('format')
            ->with($this->callback(function ($record) use ($exception) {
                return $record['context']['exception'] === $exception
                    && isset($record['context']['additional_info'])
                    && is_array($record['context']['additional_info']);
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
            ->with($this->callback(function ($records) use ($exception) {
                return $records[0]['context']['exception'] === $exception
                    && isset($records[0]['context']['additional_info'])
                    && is_array($records[0]['context']['additional_info']);
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
                return $record['context']['exception'] === $exception
                    && !isset($record['context']['additional_info']);
            }))
            ->willReturnArgument(0);

        $formatter = new EngineBlock_Log_Monolog_Formatter_AdditionalInfoFormatter($decoratedFormatter);
        $formatter->format(array('context' => array('exception' => $exception)));
    }

    public function testItDoesntOverwriteTheAdditionalInfoWhenAlreadyPresent()
    {
        $exception = new Exception('message');

        /** @var MockObject|FormatterInterface $decoratedFormatter */
        $decoratedFormatter = $this->getMockBuilder('Monolog\Formatter\FormatterInterface')->getMock();
        $decoratedFormatter->expects($this->once())
            ->method('format')
            ->with($this->callback(function ($record) use ($exception) {
                return $record['context']['exception'] === $exception
                    && $record['context']['additional_info'] === 3;
            }))
            ->willReturnArgument(0);

        $formatter = new EngineBlock_Log_Monolog_Formatter_AdditionalInfoFormatter($decoratedFormatter);
        $formatter->format(array('context' => array('exception' => $exception, 'additional_info' => 3)));
    }
}
