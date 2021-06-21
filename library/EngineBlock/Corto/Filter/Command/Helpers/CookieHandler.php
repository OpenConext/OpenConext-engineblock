<?php

class EngineBlock_Corto_Filter_Command_Helpers_CookieHandler
{
    /**
     * Clears the cookie for the specified name, path and domain.
     *
     * @param string $cookieName the cookie name
     * @param string $cookiePath the cookie path
     * @param string $cookieDomain the cookie domain
     */
    public function clearCookie($cookieName, $cookiePath, $cookieDomain)
    {
        setcookie($cookieName, '', -1, $cookiePath, $cookieDomain);
    }
}
