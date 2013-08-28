<?php

/**
 * Class for binding module specific exceptions.
 * @author Boy
 */
class EngineBlock_Corto_Module_Bindings_Exception extends EngineBlock_Corto_ProxyServer_Exception
{
    public function __construct($message, $severity = self::CODE_ERROR, Exception $previous = null)
    {
        parent::__construct($message, $severity, $previous);
    }
}