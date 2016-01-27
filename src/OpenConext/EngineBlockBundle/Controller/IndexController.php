<?php

namespace OpenConext\EngineBlockBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_View;
use Symfony\Component\HttpFoundation\Response;

class IndexController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;

    /**
     * @var EngineBlock_View
     */
    private $engineBlockView;

    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        EngineBlock_View $engineBlockView
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->engineBlockView                 = $engineBlockView;
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function indexAction()
    {
        return new Response($this->engineBlockView->render('Authentication/View/Index/Index.phtml'));
    }
}
