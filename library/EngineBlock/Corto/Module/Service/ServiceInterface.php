<?php

use Symfony\Component\HttpFoundation\Request;

interface EngineBlock_Corto_Module_Service_ServiceInterface
{
    /**
     * @param $serviceName
     * @param Request $httpRequest
     */
    public function serve($serviceName, Request $httpRequest);
}
