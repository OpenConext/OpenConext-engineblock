<?php

namespace OpenConext\EngineBlockBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

/**
 * @SuppressWarnings(PHPMD.Superglobals) see docblock at logoutAction
 */
class LogoutController
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Keep in mind that SF is set to be stateless. The EngineBlock application
     * manages the sessions (for now). Therefore we destroy these the same way
     * as is being done in EB4
     *
     * @param Request $request
     * @return Response
     */
    public function logoutAction(Request $request)
    {
        $response = new Response($this->twig->render('@theme/Logout/View/Index/index.html.twig'));

        if (empty($request->getSession()->all())) {
            return $response;
        }

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
