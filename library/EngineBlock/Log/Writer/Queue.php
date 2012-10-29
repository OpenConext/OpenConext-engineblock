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

class EngineBlock_Log_Writer_Queue extends Zend_Log_Writer_Abstract
{
    /**
     * Log message queue storage
     *
     * @var EngineBlock_Log_Writer_Queue_SessionStorage
     */
    protected $_sessionStorage = null;

    /**
     * @var Zend_Log_Writer_Abstract $_writer
     */
    protected $_target = null;

    /**
     * @param EngineBlock_Log $target
     */
    public function __construct(EngineBlock_Log $target)
    {
        $this->_target = $target;
    }

    /**
     * Lazy-load queue object
     *
     * @return EngineBlock_Log_Writer_Queue_SessionStorage
     */
    public function getSessionStorage()
    {
        if ($this->_sessionStorage === null) {
            // try to resume queue from session
            $this->_sessionStorage = new EngineBlock_Log_Writer_Queue_SessionStorage();
        }

        return $this->_sessionStorage;
    }

    /**
     * Construct a Zend_Log driver
     *
     * @param  array|Zend_Config $config
     * @return Zend_Log_FactoryInterface
     */
    public static function factory($config)
    {
        $options = self::_parseConfig($config);

        if (empty($options['targetLog'])) {
            $target = new EngineBlock_Log();
            $target->addWriter(
                new Zend_Log_Writer_Null()
            );
        } else {
            // queue will be flushed to this writer
            $target = EngineBlock_Log::factory($options['targetLog']);
        }

        $writer = new self($target);

        return $writer;
    }

    /**
     * Flush queue to target log
     *
     * @param string $message OPTIONAL reason of flush (will be prepended to queue)
     * @return EngineBlock_Log_Writer_Queue
     */
    public function flush($message = null)
    {
        $queue = $this->getSessionStorage()->getQueue();

        if ($queue->count() === 0) {
            return $this; // nothing to flush
        }

        if (!$message && $this->getSessionStorage()->getForceFlush()) {
            $message = 'additional debugging enabled';
        }

        $this->_target->info(sprintf(
            'FLUSHING %d LOG MESSAGES IN SESSION QUEUE (%s)',
            $queue->count(), $message ?: 'reason unknown'
        ));

        while ($event = end($queue)) {
            $this->_target->writeEvent(
                $this->_enrichLogEvent($event)
            );

            $key = key($queue);
            $queue->offsetUnset($key);
        }

        $this->_target->info('END OF LOG MESSAGE QUEUE');

        return $this;
    }

    /**
     * Clear the queue
     *
     * @return EngineBlock_Log_Writer_Queue
     */
    public function clear()
    {
        $queue = $this->getSessionStorage()->getQueue();
        foreach ($queue as $key => $event) {
            $queue->offsetUnset($key);
        }
        return $this;
    }

    /**
     * Sets the original log date in the message
     *
     * @param array $event
     * @return array
     */
    protected function _enrichLogEvent(array $event)
    {
        $event['message'] = sprintf(
            '> QUEUED TIMESTAMP: %s| %s',
            $event['timestamp'],
            $event['message']
        );

        return $event;
    }

    /**
     * Add a message to the queue
     *
     * @param array $event
     * @return EngineBlock_Log_Writer_Queue
     */
    protected function _write($event)
    {
        $storage = $this->getSessionStorage();

        if ($storage->getForceFlush()) {
            $this->_target->writeEvent($event);
        } else {
            $storage->getQueue()->append($event);
        }

        return $this;
    }
}
