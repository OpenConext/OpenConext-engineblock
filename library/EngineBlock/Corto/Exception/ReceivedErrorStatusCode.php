<?php

class EngineBlock_Corto_Exception_ReceivedErrorStatusCode extends EngineBlock_Exception
{
    public function __construct($message, $severity = self::CODE_ERROR, Exception $previous = null)
    {
        parent::__construct($message, $severity, $previous);
    }

}