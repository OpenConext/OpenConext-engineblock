<?php
 
abstract class Corto_Module_Abstract 
{
    /**
     * @var Corto_ProxyServer
     */
    protected $_server;

    public function __construct(Corto_ProxyServer $server)
    {
        $this->_server = $server;
    }
}
