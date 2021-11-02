<?php

/**
 * Copyright 2021 Stichting Kennisnet
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

use OpenConext\EngineBlock\Service\CookieService;


class EngineBlock_Corto_Filter_Command_SsoNotificationCookieFilter extends EngineBlock_Corto_Filter_Command_Abstract
{
    private $_ssoNotificationCookieName = "ssonot";

    /**
     * @var CookieService
     */
    private $_cookieService;

    /**
     * @var EngineBlock_Application_DiContainer
     */
    private $_diContainer;

    public function __construct(
        CookieService $cookieService,
        EngineBlock_Application_DiContainer $diContainer
    ) {
        $this->_cookieService = $cookieService;
        $this->_diContainer = $diContainer;
    }

    public function execute(): void
    {
        if (!$this->_diContainer->getFeatureConfiguration()->isEnabled('eb.enable_sso_notification')) {
            return;
        }

        if (!is_null($this->_diContainer->getSymfonyRequest()->cookies->get($this->_ssoNotificationCookieName))) {
            $this->_cookieService->clearCookie(
                $this->_ssoNotificationCookieName,
                $this->_diContainer->getCookiePath(),
                $this->_diContainer->getCookieDomain()
            );
        }
    }
}
