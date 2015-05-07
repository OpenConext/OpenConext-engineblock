<?php

class Logout_Controller_Index extends EngineBlock_Controller_Abstract
{
    public function indexAction()
    {
        $this->destroySession();
    }

    /**
     * Destroys session and removes it's cookie
     */
    private function destroySession()
    {
        if (empty($_SESSION)) {
            return;
        }

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }
}
