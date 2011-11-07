<?php

/**
 * @internal require interface
 */
require_once "Interface.php";

/**
 * A logger class that logs to Syslog.
 * @author Mads, Ivo
 */
class Corto_Log_Syslog implements Corto_Log_Interface
{    
    /**
     * Default constructor; Opens the syslog for writing.
     */
    public function __construct()
    {
        openlog("Corto", LOG_PID | LOG_PERROR, LOG_LOCAL0);
    }
    
    /**
     * The syslog logger ignores calls to setId.
     * @param String $id
     */
    public function setId($id) 
    {
    }

    /**
     * Log a message of type debug
     * @param String $message
     */
    public function debug($message) 
    {
        syslog(LOG_DEBUG, $message);
    }
    
    /**
     * Log a message of type error
     * @param String $message
     */
    public function err($message)
    {
        syslog(LOG_ERR, $message);
    }

    /**
     * Log a message of type warning
     * @param String $message
     */
    public function warn($message)
    {
        syslog(LOG_WARNING, $message);
    }
}
