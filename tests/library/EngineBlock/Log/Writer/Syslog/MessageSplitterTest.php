<?php
class EngineBlock_Log_Writer_Syslog_MessageSplitterTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {

    }

    /**
     * @dataProvider messageProvider
     */
    public function testMessageParsingIsCorrect($event, $expectedMessage)
    {
        $syslogMessageSplitter = new EngineBlock_Log_Writer_Syslog_MessageSplitter(1024);
        $chunks = $syslogMessageSplitter->split($event);

//        $this->assertEquals($expectedPrefix, $chunks[0]['prefix']);
        $this->assertEquals($expectedMessage, $chunks[0]['message']);
    }

    public function messageProvider()
    {
        return array(
            array(
                'event' => array(
                    'message' => 'EB[2fg8dc6i39etfmfmgm2hc989c1][51123ccdbc9d6] FLUSHING 72 LOG MESSAGES IN SESSION QUEUE (error caught)'
                ),
                'expectedMessage' => 'EB[2fg8dc6i39etfmfmgm2hc989c1][51123ccdbc9d6] FLUSHING 72 LOG MESSAGES IN SESSION QUEUE (error caught)'
            ),
            array(
                'event' => array(
                    'message' => 'QUEUED TIMESTAMP: 2013-02-06T12:21:49+01:00| EB[2fg8dc6i39etfmfmgm2hc989c1][51123ccc0c925] Identifier "filterCommandFactory" is not defined. [dumped 12 objects]'
                ),
                'expectedMessage' => 'QUEUED TIMESTAMP: 2013-02-06T12:21:49+01:00| EB[2fg8dc6i39etfmfmgm2hc989c1][51123ccc0c925] Identifier "filterCommandFactory" is not defined. [dumped 12 objects]'
            )
        );
    }
}
