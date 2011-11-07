<?php

/**
 * The abstract base class for all Corto internal and extension Modules.
 * @author Boy
 */
abstract class Corto_Module_Abstract
{
    /**
     * A reference to the Corto_ProxyServer to which this module belongs.
     * @var Corto_ProxyServer
     */
    protected $_server;

    /**
     * Construct a module, passing in a reference to the Corto_ProxyServer
     * to which this module belongs
     * @param Corto_ProxyServer $server
     */
    public function __construct(Corto_ProxyServer $server)
    {
        $this->_server = $server;
    }
}