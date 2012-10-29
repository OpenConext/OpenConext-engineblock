<?php

class EngineBlock_Log_Writer_Queue_Storage_Process implements EngineBlock_Log_Writer_Queue_Storage_Interface
{
    /**
     * @var bool
     */
    protected $_forceFlush = false;

    /**
     * @var ArrayObject
     */
    protected $_queue;

    public function __construct()
    {
        $this->_queue = new ArrayObject();
    }

    /**
     * @return ArrayObject
     */
    public function getQueue()
    {
        return $this->_queue;
    }

    /**
     * Set 'force flush' flag, will be written to session
     * and queue will be flushed. New log messages
     * will be logged immediately
     *
     * @param bool $enabled OPTIONAL defaults to true
     * @return EngineBlock_Log_Writer_Queue_Storage_Process
     */
    public function setForceFlush($enabled = true)
    {
        $this->_forceFlush = $enabled;
        return $this;
    }

    /**
     * Read 'force_flush' setting from session
     *
     * @return bool
     */
    public function getForceFlush()
    {
        return $this->_forceFlush;
    }
}