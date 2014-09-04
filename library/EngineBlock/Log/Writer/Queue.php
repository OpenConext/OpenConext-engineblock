<?php

class EngineBlock_Log_Writer_Queue extends Zend_Log_Writer_Abstract
{
    /**
     * Log message queue storage
     *
     * @var EngineBlock_Log_Writer_Queue_Storage_Session
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
     * @return EngineBlock_Log_Writer_Queue_Storage_Interface
     */
    public function getStorage()
    {
        if ($this->_sessionStorage === null) {
            if (php_sapi_name() === 'cli') {
                $this->_sessionStorage = new EngineBlock_Log_Writer_Queue_Storage_Process();
            }
            else {
                // try to resume queue from session
                $this->_sessionStorage = new EngineBlock_Log_Writer_Queue_Storage_Session();
            }
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
        $queue = $this->getStorage()->getQueue();

        if ($queue->count() === 0) {
            return $this; // nothing to flush
        }

        if (!$message && $this->getStorage()->getForceFlush()) {
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
        $queue = $this->getStorage()->getQueue();
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
        $storage = $this->getStorage();

        if ($storage->getForceFlush()) {
            $this->_target->writeEvent($event);
        } else {
            $storage->getQueue()->append($event);
        }

        return $this;
    }
}
