<?php

/**
 * Generic interface for Corto logging 
 * @author Boy
 */
interface Corto_Log_Interface 
{
    /**
     * Set a unique id to separate log entries. Can be used to create separate
     * logs for specific session ids etc. Not all logging engines may support
     * this (that is: they will support the setId call but will ignore it).
     * Note that all subsequent calls to err(), warn() or debug() will write to 
     * the file that was indicated by setId.
     * @param String $id
     */
    public function setId($id);
    
    /**
     * Log a message of type error.
     * @param String $message
     */
    public function err($message);
    
    /**
     * Log a message of type warning
     * @param String $message
     */
    public function warn($message);
    
    /**
     * Log a message of type debug
     * @param String $message
     */
    public function debug($message);
}