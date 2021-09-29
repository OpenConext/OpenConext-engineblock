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
