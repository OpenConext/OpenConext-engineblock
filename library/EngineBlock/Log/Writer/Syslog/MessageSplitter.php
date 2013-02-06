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
     * Called by log(), this method splits up each $message into separate
     * messages and logs them to parent::log(). Message size is determined
     * by EngineBlock_Log::MESSAGE_SPLIT_SIZE.
     *
     * @param  string $prefix       Message prefix
     * @param  string $message      Message to log
     * @return array
     */
    public function split($prefix, $message)
    {
        $baseSplitLength = $this->messageSplitSize - 24;
        $splitLength = $baseSplitLength - strlen($prefix);

        if ($splitLength < 1) {
            throw new InvalidArgumentException('Cannot split this message, prefix is (almost) longer than split size');
        }

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
