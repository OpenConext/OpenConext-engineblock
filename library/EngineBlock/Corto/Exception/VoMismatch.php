<?php

class EngineBlock_Corto_Exception_VoMismatch extends EngineBlock_Exception
{
    public function __construct($message, Exception $previous = null)
    {
        parent::__construct($message, self::CODE_NOTICE, $previous);
    }
}