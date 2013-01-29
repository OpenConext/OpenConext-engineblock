<?php

abstract class EngineBlock_Corto_Module_Service_Abstract implements EngineBlock_Corto_Module_Service_Interface
{
    /** @var \EngineBlock_Corto_ProxyServer */
    protected $_server;

    public function __construct(EngineBlock_Corto_ProxyServer $server)
    {
        $this->_server = $server;
        $this->init();
    }

    /**
     * Override in concrete class for initializing specific values etc.
     */
    protected function init()
    {
    }
}