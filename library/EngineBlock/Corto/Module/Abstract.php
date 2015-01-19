<?php

/**
 * The abstract base class for all Corto internal and extension Modules.
 * @author Boy
 */
abstract class EngineBlock_Corto_Module_Abstract
{
    /**
     * A reference to the Corto_ProxyServer to which this module belongs.
     * @var EngineBlock_Corto_ProxyServer
     */
    protected $_server;

    /**
     * Construct a module, passing in a reference to the Corto_ProxyServer
     * to which this module belongs
     * @param EngineBlock_Corto_ProxyServer $server
     */
    public function __construct(EngineBlock_Corto_ProxyServer $server)
    {
        $this->_server = $server;
    }
}
