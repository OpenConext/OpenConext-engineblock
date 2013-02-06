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
            ),
            array(
                array(
                    'message' => '> QUEUED TIMESTAMP: 2013-02-06T14:31:16+01:00| EB[5eip02tcmfepv8mo9o0atouns2][51125b23e2507][DUMP \'previous exception\' (2/2)] Exception Object\n(\n    [message:protected] => test\n    [string:Exception:private] => \n    [code:protected] => 0\n    [file:protected] => /opt/www-on-host/OpenConext-engineblock/application/modules/Authentication/Controller/Proxy.php\n    [line:protected] => 36\n    [trace:Exception:private] => Array\n        (\n            [0] => Array\n                (\n                    [fun"...'
                ),
                'expectedResult' => array(
                    'prefix'    => '> QUEUED TIMESTAMP: 2013-02-06T14:31:16+01:00| EB[5eip02tcmfepv8mo9o0atouns2][51125b23e2507][DUMP \'previous exception\' (2/2)]',
                    'message' => ' Exception Object\n(\n    [message:protected] => test\n    [string:Exception:private] => \n    [code:protected] => 0\n    [file:protected] => /opt/www-on-host/OpenConext-engineblock/application/modules/Authentication/Controller/Proxy.php\n    [line:protected] => 36\n    [trace:Exception:private] => Array\n        (\n            [0] => Array\n                (\n                    [fun"...'
                )
            ),
            array(
                array(
                    'message' => '> QUEUED TIMESTAMP: 2013-02-06T15:59:52+01:00| EB[5eip02tcmfepv8mo9o0atouns2][51126fe79b93d][DUMP \'previous exception\' (12/12)] InvalidArgumentException Object...[message] => EB[5eip02tcmfepv8mo9o0atouns2][51127c84710d6][DUMP \'Response\' (5/9)]TOO LONG: > QUEUED TIMESTAMP: 2013-02-06T16:53:42+01:00| EB['

                ),
                'expectedResult' => array(
                    'prefix'    => '> QUEUED TIMESTAMP: 2013-02-06T15:59:52+01:00| EB[5eip02tcmfepv8mo9o0atouns2][51126fe79b93d][DUMP \'previous exception\' (12/12)]',
                    'message' => ' InvalidArgumentException Object...[message] => EB[5eip02tcmfepv8mo9o0atouns2][51127c84710d6][DUMP \'Response\' (5/9)]TOO LONG: > QUEUED TIMESTAMP: 2013-02-06T16:53:42+01:00| EB['
                ),
            )
        );
    }
}
