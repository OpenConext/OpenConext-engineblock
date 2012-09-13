<?php

class EngineBlock_Corto_XmlToArray_Exception extends EngineBlock_Corto_ProxyServer_Exception
{
    public function __construct($message, $severity = self::CODE_NOTICE, Exception $previous = null)
    {
        parent::__construct($message, $severity, $previous);
    }
}