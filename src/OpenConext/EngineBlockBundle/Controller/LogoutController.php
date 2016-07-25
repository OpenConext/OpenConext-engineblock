<?php

namespace OpenConext\EngineBlockBundle\Controller;

use EngineBlock_View;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.Superglobals) see docblock at logoutAction
 */
class LogoutController
{
    /**
     * @var EngineBlock_View
     */
    private $engineBlockView;

    public function __construct(EngineBlock_View $engineBlockView)
    {
        $this->engineBlockView = $engineBlockView;
    }

    /**
     * Keep in mind that SF is set to be stateless. The EngineBlock application
     * manages the sessions (for now). Therefore we destroy these the same way
     * as is being done in EB4
     *
     * @return Response
     */
    public function logoutAction()
    {
        $response = new Response($this->engineBlockView->render('Logout/View/Index/Index.phtml'));

        if (empty($_SESSION)) {
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
