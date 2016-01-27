<?php

namespace OpenConext\EngineBlockBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Adapter;
use OpenConext\EngineBlockBridge\ResponseFactory;

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

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function debugSpConnectionAction()
    {
        $proxyServer = new EngineBlock_Corto_Adapter();

        $proxyServer->debugSingleSignOn();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }
}
