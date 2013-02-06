<?php
class EngineBlock_Log_Writer_Syslog_MessageParserTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {

    }

    /**
     * @dataProvider eventProvider
     */
    public function testMessageParsingIsCorrect(array $event, array $expectedResult)
    {
        $syslogMessageParser = new EngineBlock_Log_Writer_Syslog_MessageParser();
        $this->assertEquals($expectedResult, $syslogMessageParser->parse($event));
    }

    public function eventProvider()
    {
        return array(
            array(
                array(
                    'message' => 'non matching message'
                ),
                'expectedResult' => array(
                    'prefix' => 'PREFIX REMOVED BY PARSER',
                    'message' => 'MESSAGE REMOVED BY PARSER'
                )
            ),
            array(
                array(
                    'message' => 'EB[2fg8dc6i39etfmfmgm2hc989c1][51123ccdbc9d6] FLUSHING 72 LOG MESSAGES IN SESSION QUEUE (error caught)'
                ),
                'expectedResult' => array(
                    'prefix' => 'EB[2fg8dc6i39etfmfmgm2hc989c1][51123ccdbc9d6]',
                    'message' => ' FLUSHING 72 LOG MESSAGES IN SESSION QUEUE (error caught)'
                )
            ),
            array(
                array(
                    'message' => 'QUEUED TIMESTAMP: 2013-02-06T12:21:49+01:00| EB[2fg8dc6i39etfmfmgm2hc989c1][51123ccc0c925] Identifier "filterCommandFactory" is not defined. [dumped 12 objects]'
                ),
                'expectedResult' => array(
                    'prefix' => 'QUEUED TIMESTAMP: 2013-02-06T12:21:49+01:00| EB[2fg8dc6i39etfmfmgm2hc989c1][51123ccc0c925]',
                    'message' => ' Identifier "filterCommandFactory" is not defined. [dumped 12 objects]'
                )
            )
        );
    }
}
