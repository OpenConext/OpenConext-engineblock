<?php

/**
 * @internal require interface
 */
require_once "Interface.php";

/**
 * A logger class that logs different types of events to different logs.
 * @author Boy
 */
class Corto_Log_Composite implements Corto_Log_Interface
{
    const DEFAULT_LOG = 'Corto_Log_Dummy';

    /**
     * @var Corto_Log_Interface
     */
    protected $_errorLog;

    /**
     * @var Corto_Log_Interface
     */
    protected $_warnLog;

    /**
     * @var Corto_Log_Interface
     */
    protected $_debugLog;

    /**
     * Create a composite log, logs default to the Dummy log
     */
    public function __construct($errorLog = null, $warnLog = null, $debugLog = null)
    {
        $defaultLog = self::DEFAULT_LOG;

        // Log for errors
        if ($errorLog !== null) {
            $this->_errorLog = $errorLog;
        }
        else {
            $this->_errorLog = new $defaultLog();
        }

        // Log for warnings
        if ($warnLog !== null) {
            $this->_warnLog = $warnLog;
        }
        else {
            $this->_warnLog = new $defaultLog();
        }

        // Log for debugging
        if ($debugLog !== null) {
            $this->_debugLog = $debugLog;
        }
        else {
            $this->_debugLog = new $defaultLog();
        }
    }

    /**
     * The syslog logger ignores calls to setId.
     * @param String $id
     */
    public function setId($id)
    {
        $this->_debugLog->setId($id);
        $this->_errorLog->setId($id);
        $this->_warnLog->setId($id);
    }

    /**
     * Log a message of type debug
     * @param String $message
     */
    public function debug($message)
    {
        return $this->_debugLog->debug($message);
    }

    /**
     * Log a message of type error
     * @param String $message
     */
    public function err($message)
    {
        return $this->_errorLog->err($message);
    }

    /**
     * Log a message of type warning
     * @param String $message
     */
    public function warn($message)
    {
        return $this->_warnLog->warn($message);
    }
}
