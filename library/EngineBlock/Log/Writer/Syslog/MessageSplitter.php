<?php
class EngineBlock_Log_Writer_Syslog_MessageSplitter
{
    /** @var int */
    private $messageSplitSize;

    public function __construct($messageSplitSize)
    {
        $this->messageSplitSize = $messageSplitSize;
    }

    /**
     * Splits one message into several chunks
     *
     * @param array $event
     * @return array
     */
    public function split(array $event)
    {
        $parsed = $this->_parseMessage($event);

        $chunks = $this->_splitLogMessage(
            $parsed['prefix'], $parsed['message']
        );

        $messages = array();
        foreach ($chunks as $chunk) {
            $event['message'] = $chunk;
            $messages[] = $event;
        }

        return $messages;
    }

    /**
     * @param array $event
     * @return array
     */
    protected function _parseMessage($event)
    {
        $message = isset($event['message'])
            ? $this->_normalizeMessage($event['message']) : '';

        preg_match_all(
            '/(.*\[[a-zA-Z0-9 ]+\]\[[a-zA-Z0-9 ]+\](\[DUMP[^\]]*\])?)( .*)/',
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

    /**
     * Called by log(), this method splits up each $message into separate
     * messages and logs them to parent::log(). Message size is determined
     * by EngineBlock_Log::MESSAGE_SPLIT_SIZE.
     *
     * @param  string $prefix       Message prefix
     * @param  string $message      Message to log
     * @return array
     */
    protected function _splitLogMessage($prefix, $message)
    {
        $baseSplitLength = $this->messageSplitSize - 24;
        $splitLength = $baseSplitLength - strlen($prefix);

        // split message (approx $this->$messageSplitSize, never more) into smaller messages
        $messages = array();
        $chunks = str_split(
            $message,
            $splitLength
        );

        // log individual chunks
        foreach ($chunks as $key => $chunk) {
            if (count($chunks) > 1) {
                if (($key === 0)) {
                    // this is the first chunk of a multi-chunk message,
                    // mark start
                    $messages[] = $prefix . '!CHUNKSTART>' . $chunk;
                } else if ($key === (count($chunks) - 1)) {
                    // this is the last chunk of a multi-chunk message,
                    // mark end
                    $messages[] = $prefix . '!CHUNKEND>' . $chunk;
                } else {
                    // this is a chunk, but not the first or last,
                    // mark this as a chunk so we can concat all chunks
                    // for presentation
                    $messages[] = $prefix . '!CHUNK>' . $chunk;
                }
            } else {
                $messages = (array)($prefix . $chunk);
            }
        }

        return $messages;
    }
}
