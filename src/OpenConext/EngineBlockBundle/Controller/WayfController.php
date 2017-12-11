<?php

namespace OpenConext\EngineBlockBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Adapter;
use EngineBlock_View;
use OpenConext\EngineBlockBridge\ResponseFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function processWayfAction()
    {
        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->processWayf();

        return ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function helpDiscoverAction()
    {
        return new Response($this->engineBlockView->render('Authentication/View/IdentityProvider/HelpDiscover.phtml'));
    }

    /**
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function cookieAction(Request $request)
    {
        $application = $this->engineBlockApplicationSingleton;
        if (($application->getDiContainer()->getRememberChoice() === true)) {
            $postData = $request->request->all();
            $cookiesSet = $request->cookies->all();
            $cookies = array('main', 'rememberchoice', 'lang', 'selectedidps');
            $response = new Response();
            $removal = false;
            $all = false;
            if (array_key_exists('remove_all', $postData)) {
                foreach ($cookies as $cookie) {
                    if (array_key_exists($cookie, $cookiesSet)) {
                        unset($cookiesSet[$cookie]);
                        $response->headers->clearCookie($cookie);
                    }
                }
                // Clear all session-data on the server
                session_start();
                session_destroy();
                $removal = true;
                $all = true;
            } else if (!empty($postData)) {
                foreach ($cookies as $cookie) {
                    if (array_key_exists('remove_' . $cookie, $postData)) {
                        unset($cookiesSet[$cookie]);
                        $response->headers->clearCookie($cookie);
                        $removal = true;
                        break;
                    }
                }
            }
            $viewData = ['removal' => $removal, 'all' => $all, 'cookiesSet' => $cookiesSet];
            return $response->setContent(
                $this->engineBlockView->setData($viewData)->render('Authentication/View/IdentityProvider/RemoveCookies.phtml')
            );
        }
        return new Response($this->engineBlockView->render('Default/View/Error/NotFound.phtml'), 404);
    }
}
