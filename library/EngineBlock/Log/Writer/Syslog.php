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
