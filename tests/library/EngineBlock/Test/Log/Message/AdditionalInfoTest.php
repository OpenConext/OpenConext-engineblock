<?php

final class EngineBlock_Log_Message_AdditionalInfoTest extends PHPUnit_Framework_TestCase
{
    public function testItCorrectlyDeterminesTheExceptionsSeverity()
    {
        $exception = new EngineBlock_Exception('message', EngineBlock_Exception::CODE_ALERT);
        $additionalInfo = EngineBlock_Log_Message_AdditionalInfo::createFromException($exception);

        $this->assertSame('ALERT', $additionalInfo->getSeverity());
    }
}
