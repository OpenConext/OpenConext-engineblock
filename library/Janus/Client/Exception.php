<?php

class Janus_Client_Exception extends EngineBlock_Exception
{
    public function __construct($message, $severity = self::CODE_ALERT, Exception $previous = null)
    {
        parent::__construct($message, $severity, $previous);
    }
}