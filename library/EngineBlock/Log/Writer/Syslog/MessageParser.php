<?php
class EngineBlock_Log_Writer_Syslog_MessageParser
{
    /**
     * @param array $event
     * @return array
     */
    public function parse(array $event)
    {
        $message = isset($event['message'])  ? $this->_normalizeMessage($event['message']) : '';

        preg_match_all(
            '/' .
                '('.
                    '[^\]]*'. //  .... (anything but a ]), followed by:
                    '\[[a-zA-Z0-9 ]+\]'. // [ ... ]
                    '\[[a-zA-Z0-9 ]+\]'. // [ ... ]
                    '(\[DUMP[^\]]*\])?'. // Optionally: [DUMP ...]
                ')'.
                '( .*)'. // Anything, starting with a space.
                '/',
            $message,
            $matches
        );

        if (!isset($matches[1][0]) || !isset($matches[3][0])) {
            return array(
                'prefix' => 'P[' . time() . '][' . rand(0, 1000000) . ']',
                'message' => $message
            );
        }

        return array(
            'prefix'  => $matches[1][0],
            'message' => $matches[3][0],
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
     * @param string $message structure to dump
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
