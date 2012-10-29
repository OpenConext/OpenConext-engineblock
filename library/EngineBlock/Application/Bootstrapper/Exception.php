<?php

class EngineBlock_Application_Bootstrapper_Exception extends EngineBlock_Exception
{
    public function __construct($message, Exception $previous = null)
    {
        parent::__construct($message, self::CODE_ALERT, $previous);
    }
}