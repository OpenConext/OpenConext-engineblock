<?php

use Mockery as m;
use Monolog\Formatter\FormatterInterface;
use OpenConext\EngineBlock\Logger\Formatter\AdditionalInfoFormatter;
use PHPUnit_Framework_TestCase as TestCase;

class AdditionalInfoFormatterTest extends TestCase
{
    /**
     * @test
     * @group EngineBlock
     * @group Logger
     */
    public function testItAddsAdditionalInfoWhenAnExceptionIsPresent()
    {
        $exception = new EngineBlock_Exception('message', EngineBlock_Exception::CODE_EMERGENCY);
        /** @var MockObject|FormatterInterface $decoratedFormatter */
        $decoratedFormatter = $this->getMockBuilder('Monolog\Formatter\FormatterInterface')->getMock();
        $decoratedFormatter->expects($this->once())
            ->method('format')
            ->with(
                $this->callback(
                    function ($record) {
                        return is_array($record['context']['exception'])
                        && $record['context']['exception']['severity'] === 'EMERG';
                    }
                )
            )
            ->willReturnArgument(0);
        $formatter = new AdditionalInfoFormatter($decoratedFormatter);
        $formatter->format(array('context' => array('exception' => $exception)));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Logger
     */
    public function testItAddsAdditionalInfoInBatchWheneverAnExceptionIsPresent()
    {
        $exception = new EngineBlock_Exception('message');
        /** @var MockObject|FormatterInterface $decoratedFormatter */
        $decoratedFormatter = $this->getMockBuilder('Monolog\Formatter\FormatterInterface')->getMock();
        $decoratedFormatter->expects($this->once())
            ->method('formatBatch')
            ->with(
                $this->callback(
                    function ($records) {
                        return is_array($records[0]['context']['exception'])
                        && $records[0]['context']['exception']['severity'] === 'ERROR';
                    }
                )
            )
            ->willReturnArgument(0);
        $formatter = new AdditionalInfoFormatter($decoratedFormatter);
        $formatter->formatBatch(array(array('context' => array('exception' => $exception))));
    }

    /**
     * @test
     * @group EngineBlock
     * @group Logger
     */
    public function testItDoesntAdditionalInfoWhenARegularExceptionIsPresent()
    {
        $exception = new Exception('message');
        /** @var MockObject|FormatterInterface $decoratedFormatter */
        $decoratedFormatter = $this->getMockBuilder('Monolog\Formatter\FormatterInterface')->getMock();
        $decoratedFormatter->expects($this->once())
            ->method('format')
            ->with(
                $this->callback(
                    function ($record) use ($exception) {
                        return $record['context']['exception'] === $exception;
                    }
                )
            )
            ->willReturnArgument(0);
        $formatter = new AdditionalInfoFormatter($decoratedFormatter);
        $formatter->format(array('context' => array('exception' => $exception)));
    }
}
