<?php

/**
 * @internal include base class
 */
require_once "Interface.php";

/**
 * Corto logger that implements logging to a memcached server.
 * @author Boy
 */
class Corto_Log_Memcache implements Corto_Log_Interface
{
    const KEY_START           = 'corto';
    const KEY_PART_SEPARATOR  = '_';
    const KEY_POSTFIX_ERROR   = 'error';
    const KEY_POSTFIX_WARNING = 'warn';
    const KEY_POSTFIX_DEBUG   = 'debug';
    const VALUE_SEPARATOR     = PHP_EOL;

    protected $_expiry;

    /**
     * @var Memcache
     */
    protected $_memcache;

    /**
     * A unique id to separate log entries
     * @var String
     */
    protected $_id;

    /**
     * Constructs a logger that writes to the specified memcache server.
     * Note that if setId() is called than all logfiles will be written to keys
     * that include the id
     * @param String $filePath
     */
    public function __construct(Memcache $memcache, $expiry = 2505600) // 29 days
    {
        $this->_memcache = $memcache;
        $this->_expiry = $expiry;
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
    }

    /**
     * Log a message of type error.
     * @param String $message
     */
    public function err($message)
    {
        $this->_updateValue($message, self::KEY_POSTFIX_ERROR);
    }

    /**
     * Log a message of type warning
     * @param String $message
     */
    public function warn($message)
    {
        $this->_updateValue($message, self::KEY_POSTFIX_WARNING);
    }

    /**
     * Log a message of type debug
     * @param String $message
     */
    public function debug($message)
    {
        $this->_updateValue($message, self::KEY_POSTFIX_DEBUG);
    }

    /**
     * Write a line to the logfile.
     * @param String $line
     */
    protected function _updateValue($message, $keyPostfix = "")
    {
        $key = $this->_getKey($keyPostfix);

        $value = $this->_memcache->get($key);
        $value .= self::VALUE_SEPARATOR . $message;

        $this->_memcache->set($key, $value, null, $this->_expiry);
    }

    protected function _getKey($keyPostfix = "")
    {
        $key = self::KEY_START . self::KEY_PART_SEPARATOR . $keyPostfix;

        if (isset($this->_id) && $this->_id) {
            $key .= self::KEY_PART_SEPARATOR . $this->_id;
        }
        return $key;
    }
}
