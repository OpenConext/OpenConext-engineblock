<?php
class EngineBlock_Test_Log_Formatter_MailTest extends PHPUnit_Framework_TestCase
{
    protected $_testEvent;
    protected $_originalConfig;

    public function setUp()
    {
        $this->_testEvent = array(
            'priorityName'  => 'WARN',
            'priority'      => Zend_Log::WARN,
            'message'       => "Lorum ipsum SINGLEVALUESECRET MULTIVALUESECRET ARRAYVALUESECRET2 lorum ipsum",
        );
        $this->_originalConfig = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();

        EngineBlock_ApplicationSingleton::getInstance()->setConfiguration(
            new Zend_Config(array(
                    'singlePart' => 'SINGLEVALUESECRET',
                    'protect'=> array(
                        'me' => array(
                            'from' => array(
                                'badguys' => 'MULTIVALUESECRET'
                            )
                        )
                    ),
                    'array' => array(
                        'value' => array(
                            'secret' => array(
                                'ARRAYVALUESECRET1',
                                'ARRAYVALUESECRET2',
                            ),
                            'secretEmpty' => '',
                        )
                    ),
            ))
        );
    }

    public function testSinglePart()
    {
        // Test single part
        $formatter = new EngineBlock_Log_Formatter_Mail(array(
            'singlePart'
        ));
        $view = $formatter->format($this->_testEvent);
        $output = $view->message;
        $this->assertNotContains('SINGLEVALUESECRET', $output, 'Filtering for config key "singlePart"');
    }

    public function testMultiPart()
    {
        // Test multipart
        $formatter = new EngineBlock_Log_Formatter_Mail(array(
            'protect.me.from.badguys'
        ));
        $view = $formatter->format($this->_testEvent);
        $output = $view->message;
        $this->assertNotContains('MULTIVALUESECRET', $output, 'Filtering for config key "protect.me.from.badguys"');
    }

    public function testNonExistingKey()
    {
        $formatter = new EngineBlock_Log_Formatter_Mail(array(
            'non.existing.key',
            'nonExistingKey',
        ));
        $view = $formatter->format($this->_testEvent);
        $output = $view->message;
        $this->assertContains($this->_testEvent['message'], $output, "Testing that non-existing keys do nothing");
    }

    public function testArrayValue()
    {
        $formatter = new EngineBlock_Log_Formatter_Mail(array(
            'array.value.secret',
        ));
        $view = $formatter->format($this->_testEvent);
        $output = $view->message;
        $this->assertNotContains('ARRAYVALUESECRET2', $output, "Testing filtering out of keys like: key.secrets[]");
    }

    public function testEmptyValue()
    {
        $formatter = new EngineBlock_Log_Formatter_Mail(
            array('array.value.secretEmpty')
        );
        $view = $formatter->format($this->_testEvent);
        $output = $view->message;
        $this->assertContains($this->_testEvent['message'], $output, "Testing that a key with an empty value does nothing");
    }

    public function tearDown()
    {
        if (!$this->_originalConfig) {
            return true;
        }

        EngineBlock_ApplicationSingleton::getInstance()->setConfiguration(
            $this->_originalConfig
        );
    }
}