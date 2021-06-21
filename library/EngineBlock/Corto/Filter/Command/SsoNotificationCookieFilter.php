<?php

class EngineBlock_Corto_Filter_Command_SsoNotificationCookieFilter extends EngineBlock_Corto_Filter_Command_Abstract
{
    private $_ssoNotificationCookieName = "ssonot";

    /**
     * @var EngineBlock_Corto_Filter_Command_Helpers_CookieHandler
     */
    private $_cookieHandler;

    /**
     * @var EngineBlock_Application_DiContainer
     */
    private $_diContainer;

    public function __construct(EngineBlock_Corto_Filter_Command_Helpers_CookieHandler $cookieHandler,
                                EngineBlock_Application_DiContainer $diContainer)
    {
        $this->_cookieHandler = $cookieHandler;
        $this->_diContainer = $diContainer;
    }

    public function execute(): void
    {
        if (!$this->_diContainer->getFeatureConfiguration()->isEnabled('enable_sso_notification')) {
            return;
        }

        if (!is_null($this->_diContainer->getSymfonyRequest()->cookies->get($this->_ssoNotificationCookieName))) {
            $this->_cookieHandler->clearCookie($this->_ssoNotificationCookieName,
                $this->_diContainer->getCookiePath(),
                $this->_diContainer->getCookieDomain());
        }
    }
}
