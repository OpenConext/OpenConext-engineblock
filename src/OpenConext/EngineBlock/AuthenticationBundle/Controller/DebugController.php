<?php

namespace OpenConext\EngineBlock\AuthenticationBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Adapter;
use OpenConext\EngineBlock\CompatibilityBundle\Bridge\ResponseFactory;

class DebugController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    public function __construct(EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton)
    {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
    }

    public function debugSpConnectionAction()
    {
        $proxyServer = new EngineBlock_Corto_Adapter();

        $proxyServer->debugSingleSignOn();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }
}
