<?php

class EngineBlock_Log_Writer_Syslog extends Zend_Log_Writer_Syslog
{
    /**
     * Split all log messages in chunks no larger than below value
     *
     * @const int
     */
    const MESSAGE_SPLIT_SIZE = 1024;

    /**
     * Create a new instance of Zend_Log_Writer_Syslog
     *
     * @param  array|Zend_Config $config
     * @return Zend_Log_Writer_Syslog
     * @throws Zend_Log_Exception
     */
    static public function factory($config)
    {
        return new self(self::_parseConfig($config));
    }

    /**
     * Add a message to the queue
     *
     * @param array $event
     * @return EngineBlock_Log_Writer_Syslog
     */
    protected function _write($event)
    {
        // @todo Move to factory
        $messageParser = new EngineBlock_Log_Writer_Syslog_MessageParser();
        $parsed = $messageParser->parse($event);

        // @todo Move to factory
        $messageSplitter = new EngineBlock_Log_Writer_Syslog_MessageSplitter(self::MESSAGE_SPLIT_SIZE);
        $chunks = $messageSplitter->split(
            $parsed['prefix'], $parsed['message']
        );

        foreach ($chunks as $chunk) {
            $event['message'] = $chunk;
            parent::_write($event);
        }

        return $this;
    }
}
