<?php

namespace OpenConext\EngineBlock\Logger\Message;

use EngineBlock_Exception;
use PHPUnit_Framework_TestCase as TestCase;

final class AdditionalInfoTest extends TestCase
{
    /**
     * @test
     * @group EngineBlock
     * @group Logger
     */
    public function message_has_correct_severity()
    {
        $exception = new EngineBlock_Exception('message', EngineBlock_Exception::CODE_ALERT);
        $additionalInfo = AdditionalInfo::createFromException($exception);

        $this->assertSame('ALERT', $additionalInfo->getSeverity());
    }
}
