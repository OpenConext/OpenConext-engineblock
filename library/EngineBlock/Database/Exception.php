<?php

class EngineBlock_Database_Exception extends EngineBlock_Exception
{
    public function __construct($message, $severity = self::CODE_EMERGENCY, Exception $previous = null)
    {
        parent::__construct($message, $severity, $previous);
    }
}