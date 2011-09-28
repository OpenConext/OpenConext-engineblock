<?php

/**
 * @internal include base class
 */
require_once "Interface.php";

/**
 * Dummy logging interface. Set logging to 'dummy' in production environments 
 * where speed is more important than keeping a log.
 * @author Boy
 *
 */
class Corto_Log_Dummy implements Corto_Log_Interface
{    
    /**
     * The dummy logger ignores any call to setId
     * @param String $id
     */
    public function setId($id) 
    {
    }

    /**
     * The dummy logger ignores any call to debug()
     * @param String $message
     */
    public function debug($message) 
    {
    }
    
    /**
     * The dummy logger ignores any call to err()
     * @param String $message
     */
    public function err($message)
    {
    }

    /**
     * The dummy logger ignores any call to warn()
     * @param String $message
     */
    public function warn($message)
    {
    }
}
