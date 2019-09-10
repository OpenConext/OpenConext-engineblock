<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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

            return $response->setContent(
                $this->twig->render(
                    '@theme/Authentication/View/IdentityProvider/remove-cookies.html.twig',
                    [
                        'removal' => $removal,
                        'all' => $all,
                        'cookies' => ['main', 'rememberchoice', 'lang', 'selectedidps'],
                        'cookiesSet' => $cookiesSet
                    ]
                )
            );
        }

        return new Response($this->twig->render('@theme/Default/View/Error/not-found.html.twig'), 404);
    }
}
