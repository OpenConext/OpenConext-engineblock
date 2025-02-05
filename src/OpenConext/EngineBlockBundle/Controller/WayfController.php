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
use EngineBlock_Exception;
use OpenConext\EngineBlock\Service\SsoSessionService;
use OpenConext\EngineBlockBridge\ResponseFactory;
use OpenConext\EngineBlockBundle\Service\DiscoverySelectionService;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
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
     * @var SsoSessionService
     */
    private $sessionService;
    /**
     * @var DiscoverySelectionService
     */
    private $discoverySelectionService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton
     * @param Twig_Environment $twig
     * @param SsoSessionService $sessionService
     */
    public function __construct(
        EngineBlock_ApplicationSingleton $engineBlockApplicationSingleton,
        Twig_Environment $twig,
        SsoSessionService $sessionService,
        DiscoverySelectionService $discoverySelectionService,
        LoggerInterface $logger
    ) {
        $this->engineBlockApplicationSingleton = $engineBlockApplicationSingleton;
        $this->twig = $twig;
        $this->sessionService = $sessionService;
        $this->discoverySelectionService = $discoverySelectionService;
        $this->logger = $logger;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function processWayfAction(Request $request)
    {
        $proxyServer = new EngineBlock_Corto_Adapter();
        $proxyServer->processWayf();

        $response = ResponseFactory::fromEngineBlockResponse($this->engineBlockApplicationSingleton->getHttpResponse());

        $session = $request->getSession();
        if ($session === null) {
            throw new EngineBlock_Exception('Could not set discovery override, no session available!');
        }

        if ($request->request->get(DiscoverySelectionService::USED_DISCOVERY_HASH_PARAM, '') !== '') {
            $this->discoverySelectionService->registerDiscoveryHash(
                $session,
                $request->request->get(DiscoverySelectionService::USED_DISCOVERY_HASH_PARAM)
            );
        } else {
            $this->discoverySelectionService->clearDiscoveryHash($session);
        }

        return $response;
    }

    /**
     * This method is not used in the skeune theme
     *
     * @return Response
     * @throws \EngineBlock_Exception
     */
    public function helpDiscoverAction()
    {
        return new Response($this->twig->render('@theme/Authentication/View/IdentityProvider/help-discover.html.twig'));
    }

    private function getCookies(): array
    {
        $cookies = ['main', 'rememberchoice', 'lang', 'selectedidps'];

        if ($this->engineBlockApplicationSingleton->getDiContainer()->getFeatureConfiguration()
            ->hasFeature("eb.enable_sso_session_cookie")) {
            $cookies[] = SsoSessionService::SSO_SESSION_COOKIE_NAME;
        }

        return $cookies;
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
            $cookies = $this->getCookies();
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
                            if ($cookie === SsoSessionService::SSO_SESSION_COOKIE_NAME) {
                                $this->sessionService->clearSsoSessionCookie();
                            }
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
                        'cookies' => $cookies,
                        'cookiesSet' => $cookiesSet,
                    ]
                )
            );
        }

        return new Response($this->twig->render('@theme/Default/View/Error/not-found.html.twig'), 404);
    }
}
