<?php

interface EngineBlock_Corto_Module_Service_Interface
{
    public function __construct(EngineBlock_Corto_ProxyServer $server, EngineBlock_Corto_XmlToArray $xmlConverter);
    public function serve($serviceName);
}