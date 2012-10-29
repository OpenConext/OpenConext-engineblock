<?php

interface EngineBlock_Log_Writer_Queue_Storage_Interface
{
    /**
     * @return ArrayObject
     */
    public function getQueue();

    /**
     * Set 'force flush' flag, will be written to session
     * and queue will be flushed. New log messages
     * will be logged immediately
     *
     * @param bool $enabled OPTIONAL defaults to true
     * @return EngineBlock_Log_Writer_Queue_Storage_Interface
     */
    public function setForceFlush($enabled = true);

    /**
     * Read 'force_flush' setting from session
     *
     * @return bool
     */
    public function getForceFlush();
}