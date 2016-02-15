<?php

namespace OpenConext\EngineBlock\Logger\Formatter;

use EngineBlock_Exception;
use Exception;
use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;

class AdditionalInfoFormatterTest extends TestCase
{
    /**
     * @test
     * @group EngineBlock
     * @group Logger
     */
    public function additional_info_is_added_for_an_engineblock_exception()
    {
        $exception = new EngineBlock_Exception('message', EngineBlock_Exception::CODE_EMERGENCY);

        $formatter = new AdditionalInfoFormatter(new PassthruFormatter());
        $formatted = $formatter->format(array('context' => array('exception' => $exception)));

        $this->assertTrue(
            is_array($formatted['context']['exception']),
            'EngineBlock Exception representation should be converted to array'
        );
        $this->assertEquals(
            'EMERG',
            $formatted['context']['exception']['severity'],
            'Engineblock Exception code should be mapped.'
        );
    }

    /**
     * @test
     * @group EngineBlock
     * @group Logger
     */
    public function additional_info_is_added_for_engineblock_exception_when_batch_formatting()
    {
        $exception = new EngineBlock_Exception('message');

        $formatter = new AdditionalInfoFormatter(new PassthruFormatter());
        $formatted = $formatter->formatBatch(array(array('context' => array('exception' => $exception))));

        $this->assertTrue(
            is_array($formatted[0]['context']['exception']),
            'EngineBlock Exception representation should be converted to array'
        );
        $this->assertEquals(
            'ERROR',
            $formatted[0]['context']['exception']['severity'],
            'Engineblock Exception code should be mapped.'
        );
    }

    /**
     * @test
     * @group EngineBlock
     * @group Logger
     */
    public function additional_info_is_not_added_for_non_engineblock_exceptions()
    {
        $exception = new Exception('message');

        $formatter = new AdditionalInfoFormatter(new PassthruFormatter());
        $formatted = $formatter->format(array('context' => array('exception' => $exception)));

        $this->assertEquals($exception, $formatted['context']['exception']);
    }
}
