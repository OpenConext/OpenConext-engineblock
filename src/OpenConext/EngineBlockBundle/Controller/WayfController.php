<?php

namespace OpenConext\EngineBlockBundle\Controller;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Adapter;
use OpenConext\EngineBlockBridge\ResponseFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Twig_Environment;

class WayfController
{
    /**
     * @var EngineBlock_ApplicationSingleton
     */
    private $engineBlockApplicationSingleton;
    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @param EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton
     * @param Twig_Environment $twig
     */
    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        Twig_Environment $twig
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->twig = $twig;
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
        return new Response($this->twig->render('@theme/Authentication/View/IdentityProvider/help-discover.html.twig'));
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
            } else {
                if (!empty($postData)) {
                    foreach ($cookies as $cookie) {
                        if (array_key_exists('remove_'.$cookie, $postData)) {
                            unset($cookiesSet[$cookie]);
                            $response->headers->clearCookie($cookie);
                            $removal = true;
                            break;
                        }
                    }
                }
            }
            $viewData = ['removal' => $removal, 'all' => $all, 'cookiesSet' => $cookiesSet];

            return $response->setContent(
                $this->twig->setData($viewData)->render('Authentication/View/IdentityProvider/RemoveCookies.phtml')
            );
        }

        return new Response($this->twig->render('Default/View/Error/NotFound.phtml'), 404);
    }
}
