<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

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

        $parsed = $this->_parseMessage($event);

        $chunks = $this->_splitLogMessage(
            $parsed['prefix'], $parsed['message']
        );

        foreach ($chunks as $chunk) {
            $event['message'] = $chunk;

            parent::_write($event);
        }

        return $this;
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
            '/(.*\[[a-zA-Z0-9 ]+\]\[[a-zA-Z0-9 ]+\](\[DUMP\])?)( .*)/',
            $message, $matches
        );

        return array(
            'prefix' => isset($matches[1][0]) ? $matches[1][0] : '',
            'message' => isset($matches[3][0]) ? $matches[3][0] : '',
        );
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
        // split message (approx MESSAGE_SPLIT_SIZE, never more) into smaller messages
        $messages = array();
        $chunks = str_split(
            $message,
            self::MESSAGE_SPLIT_SIZE - 24 - strlen($prefix)
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
