<?php

/**
 * @internal include base class
 */
require_once "Interface.php";

/**
 * Corto logger that implements logging to a file.
 * @author Boy
 */
class Corto_Log_File implements Corto_Log_Interface
{
    /**
     * The path and name of the file to log to.
     * @var String
     */
    protected $_filePath;
    
    /**
     * A unique id to separate log entries
     * @var String
     */
    protected $_id;

    /**
     * Constructs a logger that writes to the specified file.
     * Note that if setId() is called than all logfiles will have
     * '_<id>' appended to them.
     * @param String $filePath
     */
    public function __construct($filePath)
    {
        $this->_filePath = $filePath;
    }

    /**
     * Set a unique id to separate log entries. Can be used to create separate
     * logs for specific session ids etc. Not all logging engines may support
     * this (that is: they will support the setId call but will ignore it).
     * Note that all subsequent calls to err(), warn() or debug() will write to 
     * the file that was indicated by setId.
     * @param String $id
     */
    public function setId($id)
    {
        $this->_id = $id;
        $this->_filePath .= '_' . $id;
    }

    /**
     * Log a message of type error.
     * @param String $message
     */
    public function err($message)
    {
        $this->_writeLine("[ERR] $message");
    }

    /**
     * Log a message of type warning
     * @param String $message
     */
    public function warn($message)
    {
        $this->_writeLine("[WARN] $message");
    }

    /**
     * Log a message of type debug
     * @param String $message
     */
    public function debug($message)
    {
        $this->_writeLine("[DBG] $message");
    }

    /**
     * Write a line to the logfile.
     * @param String $line
     */
    protected function _writeLine($line)
    {
        $handle = fopen($this->_filePath, 'a');
        fwrite($handle, $line . PHP_EOL);
        fclose($handle);
    }
}
