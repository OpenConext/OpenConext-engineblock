<?php
class EngineBlock_Log_Writer_Syslog_MessageParser
{
    /**
     * @param array $event
     * @return array
     */
    public function parse(array $event)
    {
        $message = isset($event['message'])
            ? $this->_normalizeMessage($event['message']) : '';

        preg_match_all(
            '/([^\]]*\[[a-zA-Z0-9 ]+\]\[[a-zA-Z0-9 ]+\](\[DUMP[^\]]*\])?)( .*)/',
            $message, $matches
        );

        return array(
            'prefix' => isset($matches[1][0]) ? $matches[1][0] : 'PREFIX REMOVED BY PARSER',
            'message' => isset($matches[3][0]) ? $matches[3][0] : 'MESSAGE REMOVED BY PARSER',
        );
    }

    /**
     * Takes $message argument and returns loggable string
     *  - newlines are replaced with \n (syslog compatible)
     *  - arrays are encoded as JSON
     *  - objects are PHP serialized
     *
     * Serialized content is prepended with '!FORMAT_[type]', this
     * notation is parsed by logparse.sh
     *
     * @param mixed data structure to dump
     * @return string
     */
    protected function _normalizeMessage($message)
    {
        // escape newlines
        $message = str_replace("\n", '\n', (string)$message);
        $message = str_replace("\r", '', (string)$message); // discard CR

        return $message;
    }
}
