<?php

abstract class EngineBlock_Corto_Module_Service_Abstract
    implements EngineBlock_Corto_Module_Service_Interface
{
    /** @var \EngineBlock_Corto_ProxyServer */
    protected $_server;

    /**
     * @var EngineBlock_Corto_XmlToArray
     */
    protected $_xmlConverter;

    /**
     * @var Twig_Environment
     */
    protected $twig;

    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        EngineBlock_Corto_XmlToArray $xmlConverter,
        Twig_Environment $twig
    ) {
        $this->_server = $server;
        $this->_xmlConverter = $xmlConverter;
        $this->twig = $twig;
    }
}
