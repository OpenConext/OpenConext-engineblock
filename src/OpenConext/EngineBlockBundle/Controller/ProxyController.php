<?php

namespace OpenConext\EngineBlockBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Adapter;
use OpenConext\EngineBlockBridge\ResponseFactory;

class ProxyController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    /**
     * @param EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton
     */
    public function __construct(EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton)
    {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function processedAssertionAction()
    {
        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->processedAssertionConsumer();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }
}
