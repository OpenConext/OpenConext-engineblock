<?php

namespace OpenConext\EngineBlock\AuthenticationBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Adapter;
use EngineBlock_View;
use OpenConext\EngineBlock\CompatibilityBundle\Bridge\ResponseFactory;
use Symfony\Component\HttpFoundation\Response;

class WayfController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;
    /**
     * @var EngineBlock_View
     */
    private $engineBlockView;

    /**
     * @param EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton
     * @param EngineBlock_View                 $engineBlockView
     */
    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        EngineBlock_View $engineBlockView
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->engineBlockView = $engineBlockView;
    }

    public function processWayfAction()
    {
        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->processWayf();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }

    public function helpDiscoverAction()
    {
        return new Response($this->engineBlockView->render('Authentication/View/IdentityProvider/HelpDiscover.phtml'));
    }
}
