<?php

/**
 * Tests for EngineBlock_Log
 *
 * @group log
 */
class EngineBlock_Test_LogTest extends PHPUnit_Framework_TestCase
{
    public $config = array('null'=>array('writerName' => 'Null'));

    public function testCanInstantiateLogObject()
    {
        $this->assertInstanceOf(
            'EngineBlock_Log',
            EngineBlock_Log::factory($this->config)
        );
    }

    public function testCanAttachObjectsOfAnyType()
    {
        EngineBlock_Log::factory($this->config)
            ->attach(array(), 'empty')
            ->attach('string', 'string')
            ->attach((object)array(), 'object');
    }


    public function testAttachedObjectsAreLoggedAfterLogCall()
    {
        EngineBlock_Log::factory($this->config)
            ->attach(array('arrayelement'), 'object');
    }

    public function testLastEventIsSet()
    {
        $log = EngineBlock_Log::factory($this->config);
        $log->log(
            'testMessage',
            1,
            array('test')
        );
        $lastEvent = $log->getLastEvent();

        $this->assertArrayHasKey('timestamp', $lastEvent);
        $this->assertEquals('testMessage', $lastEvent['message']);
        $this->assertArrayHasKey('requestid', $lastEvent);
        $this->assertEquals(1, $lastEvent['priority']);
        $this->assertEquals(array('test'), $lastEvent['info']);
    }
}