<?php

interface EngineBlock_Corto_Module_Service_Interface
{
    public function __construct(
        EngineBlock_Corto_ProxyServer $server,
        EngineBlock_Corto_XmlToArray $xmlConverter,
        Twig_Environment $twig
    );

    public function serve($serviceName);
}
