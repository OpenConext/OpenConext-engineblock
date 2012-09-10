<?php

class EngineBlock_Corto_Module_Service_Abstract implements EngineBlock_Corto_Module_Service_Interface
{
    protected $_server;

    public function __construct(EngineBlock_Corto_CoreProxy $server)
    {
        $this->_server = $server;
    }

    abstract public function serve();
}