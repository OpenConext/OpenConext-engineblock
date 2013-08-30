<?php

class EngineBlock_Corto_Module_Bindings_UnableToReceiveMessageException extends EngineBlock_Corto_Module_Bindings_Exception
{
    public function __construct($message, $severity = self::CODE_ERROR, Exception $previous = null)
    {
        parent::__construct($message, $severity, $previous);
    }

}