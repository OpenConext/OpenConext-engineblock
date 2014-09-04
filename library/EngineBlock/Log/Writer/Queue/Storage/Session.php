<?php

class EngineBlock_Log_Writer_Queue_Storage_Session implements EngineBlock_Log_Writer_Queue_Storage_Interface
{
    /**
     * @var array $_session references superglobal
     */
    protected $_session = null;

    /**
     * Load ArrayObject from session or creates new one
     * with reference to session
     *
     * @param null|array $session will use superglobal when omitted
     */
    public function __construct($session = null)
    {
        // see if session should be started
        $this->_startSession();

        if ($session === null) {
            $this->_session = $session;
        } else {
            $this->_session &= $_SESSION[__CLASS__];
        }

        if (
            (empty($this->_session['queue'])) ||
            (!$this->_session['queue'] instanceof ArrayObject)
        ) {
            // create empty queue
            $this->_session['queue'] = new ArrayObject();
        }
    }

    /**
     * @return ArrayObject
     */
    public function getQueue()
    {
        return $this->_session['queue'];
    }

    /**
     * Set 'force flush' flag, will be written to session
     * and queue will be flushed. New log messages
     * will be logged immediately
     *
     * @param bool $enabled OPTIONAL defaults to true
     * @return EngineBlock_Log_Writer_Queue_Storage_Session
     */
    public function setForceFlush($enabled = true)
    {
        $this->_session['force_flush'] = (bool)$enabled;

        return $this;
    }

    /**
     * Read 'force_flush' setting from session
     *
     * @return bool
     */
    public function getForceFlush()
    {
        return (!empty($this->_session['force_flush']));
    }

    /**
     * Try to start session if not already started
     *
     * @return EngineBlock_Log_Writer_Queue_Storage_Session
     */
    protected function _startSession()
    {
        if (!session_id()) {
            session_start();
        }

        return $this;
    }
}

