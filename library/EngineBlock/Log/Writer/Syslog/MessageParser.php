<?php

/**
 * The EngineBlock Syslog Message Parser splits a log line into prefix and message.
 *
 * It does this so the EngineBlock Syslog Message Splitter can split the message for a max length,
 * pre-pending to each chunk the proper prefix.
 */
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
                    '\[[^\]]+\]'. // [ ... ]
                    '\[[^\]]+\]'. // [ ... ]
                    '(\[DUMP[^\]]*\])?'. // Optionally: [DUMP...]
                ')'.
                '( .*)'. // Anything, starting with a space.
                '/',
            $message,
            $matches
        );

        // If for some reason we can't accurately match the actual prefix then we make one up on the spot.
        // It is important that the message gets to the logs.
        // @see https://github.com/OpenConext/OpenConext-engineblock/issues/87
        if (!isset($matches[1][0]) || !isset($matches[3][0])) {
            return array(
                'prefix' => 'EBP[' . time() . '][' . rand(0, 1000000) . ']',
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
     *
     * @param string $message Message to dump
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
