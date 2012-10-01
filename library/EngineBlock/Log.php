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

class EngineBlock_Log extends Zend_Log
{
    /**
     * Remember unique request ID
     */
    protected $_requestId = null;

    /**
     * Objects to dump
     */
    protected $_attachments = array();

    /**
     * Factory to construct the logger and one or more writers
     * based on the configuration array
     *
     * @param array $config
     *
     * @internal param array|\Zend_Config $Array or instance of Zend_Config
     * @return Zend_Log
     */
    static public function factory($config = array())
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        }

        if (!is_array($config) || empty($config)) {
            throw new EngineBlock_Exception(
                'Configuration must be an array or instance of Zend_Config',
                EngineBlock_Exception::CODE_ALERT
            );
        }

        $log = new EngineBlock_Log();

        if (!is_array(current($config))) {
            $log->addWriter(current($config));
        } else {
            foreach ($config as $writer) {
                $log->addWriter($writer);
            }
        }

        return $log;
    }

    /**
     * Log a message at a priority, overrides Zend_Log to prepend
     * session and request ID to message
     *
     * @param  mixed    $message   Message to log
     * @param  integer  $priority  Priority of message
     * @param  mixed    $additionalInfo    Extra information to log in event
     * @return void
     * @throws Zend_Log_Exception
     */
    public function log($message, $priority, $additionalInfo = null)
    {
        // see if we need to set event items, used by Mail writer
        if ($additionalInfo instanceof EngineBlock_Log_Message_AdditionalInfo) {
            $this->_setAdditionalEventItems($additionalInfo);
        }

        // add identifier to help recognize all log messages written
        // during one request
        $requestId = $this->getRequestId();

        // add session identifier
        $sessionId = session_id() ?: 'no session';

        // dump count
        $count = count($this->_attachments);
        if ($count > 0) {
            $message .= sprintf(
                ' [dumping %d object%s]', $count, ($count) ? 's' : ''
            );
        }

        // format message prefix
        $prefix = sprintf('EB[%s][%s]', $sessionId, $requestId);

        // log message
        parent::log(
            $prefix . ' ' . $message, $priority, $additionalInfo
        );

        // dump objects
        while ($data = array_shift($this->_attachments)) {
            parent::log(
                $prefix . '[DUMP] ' . $data, $priority, $additionalInfo
            );
        }

        return $this;
    }

    /**
     * Add data to append serialized after next log message
     *  - if log() is not called after attach(), data is discarded
     *
     * @param mixed $data
     * @return EngineBlock_Log
     */
    public function attach($data)
    {
        if (!is_string($data)) {
            $data = var_export($data, true);
        }

        $this->_attachments[] = $data;

        return $this;
    }

    /**
     * Write an event structure to each writer, used by Queue writer
     * to collect internal events and send them to log object later
     *
     * @param array $event
     * @return \EngineBlock_Log
     */
    public function writeEvent(array $event)
    {
        // send to each writer
        foreach ($this->_writers as $writer) {
            $writer->write($event);
        }

        return $this;
    }

    /**
     * Returns the first queue-ing writer found
     *
     * @return \EngineBlock_Log_Writer_Queue
     * @throws EngineBlock_Exception
     */
    public function getQueueWriter()
    {
        foreach ($this->_writers as $writer) {
            if (!$writer instanceof EngineBlock_Log_Writer_Queue) {
                continue;
            }

            return $writer;
        }

        // No queueing log writer registered
        return new EngineBlock_Log_Writer_Queue(
            new ArrayObject(), new EngineBlock_Log()
        );
    }

    /**
     * Returns unique ID pre request
     *
     * @return string
     */
    public function getRequestId()
    {
        if ($this->_requestId === null) {
            $this->_requestId = uniqid();
        }

        return $this->_requestId;
    }

    /**
     * @param EngineBlock_Log_Message_AdditionalInfo $additionalInfo
     */
    protected function _setAdditionalEventItems(EngineBlock_Log_Message_AdditionalInfo $additionalInfo = null)
    {
        if ($additionalInfo) {
            $additionalEvents = $additionalInfo->toArray();
            foreach ($additionalEvents as $key => $value) {
                $this->setEventItem($key, $value);
            }
        }
    }
}