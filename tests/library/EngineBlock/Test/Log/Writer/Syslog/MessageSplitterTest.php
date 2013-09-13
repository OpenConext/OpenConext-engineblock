<?php
class EngineBlock_Test_Log_Writer_Syslog_MessageSplitterTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {

    }

    /**
     * @dataProvider parsedEventProvider
     */
    public function testMessageParsingIsCorrect(array $parsedEvent, $expectedMessage)
    {
        $syslogMessageSplitter = new EngineBlock_Log_Writer_Syslog_MessageSplitter(1024);
        $chunks = $syslogMessageSplitter->split($parsedEvent['prefix'], $parsedEvent['message']);
        $this->assertEquals($expectedMessage, $chunks[0]);
    }

    public function parsedEventProvider()
    {
        return array(
            array(
                'parsedEvent' => array(
                    'prefix' => 'EB[2fg8dc6i39etfmfmgm2hc989c1][51123ccdbc9d6]',
                    'message' => ' FLUSHING 72 LOG MESSAGES IN SESSION QUEUE (error caught)'
                ),
                'expectedMessage' => 'EB[2fg8dc6i39etfmfmgm2hc989c1][51123ccdbc9d6] FLUSHING 72 LOG MESSAGES IN SESSION QUEUE (error caught)'
            ),
            array(
                'parsedEvent' => array(
                    'prefix' => 'QUEUED TIMESTAMP: 2013-02-06T12:21:49+01:00| EB[2fg8dc6i39etfmfmgm2hc989c1][51123ccc0c925]',
                    'message' => ' Identifier "filterCommandFactory" is not defined. [dumped 12 objects]'
                ),
                'expectedMessage' => 'QUEUED TIMESTAMP: 2013-02-06T12:21:49+01:00| EB[2fg8dc6i39etfmfmgm2hc989c1][51123ccc0c925] Identifier "filterCommandFactory" is not defined. [dumped 12 objects]'
            ),
            array(
                'parsedEvent' => array(
                    'prefix'    => 'QUEUED TIMESTAMP: 2013-02-06T11:42:33+01:00| EB[6c4cd5qm6cvkm34p0b9ao6uhv1][51123397cf144][DUMP \'previous exception\' (12/12)]',
                    'message' => '> InvalidArgumentException Object\n(\n    [message:protected] => Identifier "filterCommandFactory" is not defined.\n    [string:Exception:private] => \n    [code:protected] => 0\n    [file:protected] => /opt/www-on-host/OpenConext-engineblock/vendor/pimple/pimple/lib/Pimple.php\n    [line:protected] => 78\n    [trace:Exception:private] => Array\n        (\n            [0] => Array\n "...'
                ),
                'expectedMessage' => 'QUEUED TIMESTAMP: 2013-02-06T11:42:33+01:00| EB[6c4cd5qm6cvkm34p0b9ao6uhv1][51123397cf144][DUMP \'previous exception\' (12/12)]> InvalidArgumentException Object\n(\n    [message:protected] => Identifier "filterCommandFactory" is not defined.\n    [string:Exception:private] => \n    [code:protected] => 0\n    [file:protected] => /opt/www-on-host/OpenConext-engineblock/vendor/pimple/pimple/lib/Pimple.php\n    [line:protected] => 78\n    [trace:Exception:private] => Array\n        (\n            [0] => Array\n "...'
            )
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Cannot split this message, prefix is (almost) longer than split size
     */
    public function testPrefixIsTooLong()
    {
        $syslogMessageSplitter = new EngineBlock_Log_Writer_Syslog_MessageSplitter(1024);
        $prefix = str_repeat('x', 2000);
        $message = 'test';
        $chunks = $syslogMessageSplitter->split($prefix, $message);
    }
}
