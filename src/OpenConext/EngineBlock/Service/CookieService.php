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

namespace OpenConext\EngineBlock\Service;

/**
 * This class is build as wrapper on the setCookie() to be able to create unit tests around setting cookies.
 */
class CookieService
{
    /**
     * Sets the cookie for the specified name, path and domain.
     *
     * @param  string       $name
     * @param  string|mixed $value
     * @param  int          $expire
     * @param  string       $path
     * @param  string       $domain
     * @param  bool         $secure
     * @param  bool         $httpOnly
     * @return bool
     */
    public function setCookie(
        string $name,
        $value,
        int    $expire,
        string $path,
        string $domain,
        bool   $secure = true,
        bool   $httpOnly = true
    ): bool {
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Clears the cookie for the specified name, path and domain.
     *
     * @param  string $name
     * @param  string $path
     * @param  string $domain
     * @param  bool   $secure
     * @param  bool   $httpOnly
     * @return bool
     */
    public function clearCookie(
        string $name,
        string $path,
        string $domain,
        bool   $secure = true,
        bool   $httpOnly = true
    ): bool {
        return setcookie($name, '', -1, $path, $domain, $secure, $httpOnly);
    }
}
