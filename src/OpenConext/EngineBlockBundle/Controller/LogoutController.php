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

use OpenConext\EngineBlock\Service\SsoSessionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * @SuppressWarnings(PHPMD.Superglobals) see docblock at logoutAction
 */
class LogoutController
{
    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var SsoSessionService
     */
    private $ssoSessionService;

    public function __construct(Environment $twig, SsoSessionService $ssoSessionService)
    {
        $this->twig = $twig;
        $this->ssoSessionService = $ssoSessionService;
    }

    /**
     * Keep in mind that SF is set to be stateless. The EngineBlock application
     * manages the sessions (for now). Therefore we destroy these the same way
     * as is being done in EB4
     *
     * @param  Request $request
     * @return Response
     */
    public function logoutAction(Request $request)
    {
        $response = new Response($this->twig->render('@theme/Logout/View/Index/index.html.twig'));

        if (empty($request->getSession()->all())) {
            return $response;
        }

        $this->ssoSessionService->clearSsoSessionCookie();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();

        return $response;
    }
}
