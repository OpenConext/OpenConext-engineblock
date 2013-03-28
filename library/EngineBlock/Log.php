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
     * This token will be prefixed to each log message but not to attachments. This makes it easier to filter only the
     * messages from the log by running a command like:
     *
     * sudo tail -f /var/log/messages | grep 'message:'
     */
    const MESSAGE_PREFIX = '[Message %s]';

    /**
     * Remember unique request ID
     */
    protected $_requestId = null;

    /**
     * Objects to dump
     */
    protected $_attachments = array();

    /**
     * @param array $attachments
     * @return array
     */
    static public function encodeAttachments(array $attachments)
    {
        foreach ($attachments as $key => $attachment) {
            if (!is_string($attachment['message'])) {
                // allow more using memory to decode large objects
                ini_set('memory_limit', '256M');

                $attachment['message'] = print_r($attachment['message'], true);

                if (strlen($attachment['message']) > 128*1024) {
                    $attachment['message'] = substr($attachment['message'], 0, 128*1024)
                                            . '... (message truncated to 128K)';
                }
                
                $attachments[$key] = $attachment;
            }
        }

        return $attachments;
    }

    /**
     * Factory to construct the logger and one or more writers
     * based on the configuration array
     *
     * @param array $config
     *
     * @internal param array|\Zend_Config $Array or instance of Zend_Config
     * @return EngineBlock_Log
     * @throws EngineBlock_Exception
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
        // sanity checks
        if (empty($this->_writers)) {
            throw new Zend_Log_Exception('No writers were added');
        }

        if (! isset($this->_priorities[$priority])) {
            throw new Zend_Log_Exception('Bad log priority');
        }

        // see if we need to set event items, used by Mail writer
        if ($additionalInfo instanceof EngineBlock_Log_Message_AdditionalInfo) {
            $this->_setAdditionalEventItems($additionalInfo);
        }

        // pack into event required by filters and writers
        $event = array(
            'timestamp'     => date('c'),
            'message'       => $message,
            'priority'      => $priority,
            'priorityName'  => $this->_priorities[$priority],
            'info'          => $additionalInfo,
            'requestid'     => $this->getRequestId(),
        );

        // abort if rejected by the global filters
        /** @var $filter Zend_Log_Filter_Interface */
        foreach ($this->_filters as $filter) {
            if (!$filter->accept($event)) {
                return;
            }
        }

        // send to each writer
        /** @var $writer Zend_Log_Writer_Abstract */
        foreach ($this->_writers as $writer) {
            // get message array
            $writerEvent = $event;

            // Inline attachments for Mail writer
            if ($writer instanceof EngineBlock_Log_Writer_Mail) {
                $writerEvent['attachments'] = self::encodeAttachments($this->_attachments);

                $writer->write($writerEvent);

            } else {
                // Number the attachments and write them separately
                $attachments = self::encodeAttachments($this->_attachments);
                $attachmentTotal = count($attachments);
                for ($i = $attachmentTotal - 1; $i >= 0; $i--) {
                    $attachment = $attachments[$i];
                    $attachmentEvent = $event;
                    $attachmentMessagePrefix = $this->getAttachmentPrefix(
                        $attachment['name'],
                        $i + 1,
                        $attachmentTotal
                    );
                    $attachmentEvent['message'] = $attachmentMessagePrefix . $attachment['message'];

                    // log line for each file/message
                    $writer->write($attachmentEvent);
                }

                // Annotate the message
                $writerEvent['message'] = $this->getPrefix() . sprintf(self::MESSAGE_PREFIX, $event['priorityName']) . ' ' . $message . $this->getSuffix();

                // log line for each file/message
                $writer->write($writerEvent);
            }
        }
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        // add identifier to help recognize all log messages written
        // during one request
        $requestId = $this->getRequestId();

        // add session identifier
        $sessionId = session_id() ?: 'no session';

        return sprintf('EB[%s][%s] ', $sessionId, $requestId);
    }

    /**
     * @return string
     */
    public function getSuffix()
    {
        // dump count
        $count = count($this->_attachments);
        if ($count > 0) {
            return sprintf(
                ' [dumped %d object%s]', $count, ($count) ? 's' : ''
            );
        }
        else {
            return '';
        }
    }

    /**
     * @param $name
     * @param $item
     * @param $total
     * @return string
     */
    public function getAttachmentPrefix($name, $item, $total)
    {
        return trim($this->getPrefix()) . sprintf("[DUMP '%s' (%d/%d)] ", $name, $item, $total);
    }

    /**
     * Add data to append serialized after next log message
     *  - if log() is not called after attach(), data is discarded
     *
     * @param mixed $data
     * @param string $name
     * @return EngineBlock_Log
     */
    public function attach($data, $name)
    {
        $this->_attachments[] = array(
            'name' => $name,
            'message' => $data,
        );

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
        /** @var Zend_Log_Writer_Abstract $writer */
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
            new EngineBlock_Log()
        );
    }

    /**
     * Flushes log  queue
     */
    public function flushQueue()
    {
        $queue = $this->getQueueWriter();

        $queue->getStorage()
            ->setForceFlush(true);

        $queue->flush();
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
        if (!$additionalInfo) {
            return;
        }

        $additionalEvents = $additionalInfo->toArray();
        foreach ($additionalEvents as $key => $value) {
            $this->setEventItem($key, $value);
        }
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
