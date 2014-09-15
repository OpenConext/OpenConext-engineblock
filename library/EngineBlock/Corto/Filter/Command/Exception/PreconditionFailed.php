<?php

class EngineBlock_Corto_Filter_Command_Exception_PreconditionFailed extends EngineBlock_Exception
{
    public function __construct($message, $severity = self::CODE_NOTICE, Exception $previous = null)
    {
        parent::__construct($message, $severity, $previous);
    }
}