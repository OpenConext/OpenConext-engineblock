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

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * As a Service Provider I want to be able to discover whether a user can log in or is logged in via
 * Engineblock, so that I can optimize the start of the authentication process.
 *
 * When the user is successfully authenticated by the Identity Provider, Engineblock must store an SSO session cookie
 * in their browser. OpenConext's SSO query service is then able, if necessary, to verify whether this cookie exists
 * and to return the correct answer to the requesting party based on this.
 *
 * An authentication with the Identity Provider is successful when the SAML response contains:
 * `urn:oasis:names:tc:SAML:2.0:status:Success`
 */
class SsoSessionService
{
    public const SSO_SESSION_COOKIE_NAME = "sso_id";

    /**
     * @var int
     */
    private $maxAge;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $cookiePath;

    /**
     * @var string
     */
    private $cookieDomain;

    /**
     * @var CookieService
     */
    private $cookieService;

    public function __construct(
        int             $maxAge,
        string          $cookieDomain,
        string          $cookiePath,
        CookieService   $cookieService,
        LoggerInterface $logger
    ) {
        $this->maxAge = $maxAge;
        $this->cookieDomain = $cookieDomain;
        $this->cookiePath = $cookiePath;
        $this->cookieService = $cookieService;
        $this->logger = $logger;
    }

    /**
     * Retrieves the SSO Session cookie from the provided set of cookies.
     *
     * @param  ParameterBag $cookies the set of cookies to retrieve the SSO session cookie from
     * @return mixed|null the SSO notification cookie or null if not present
     */
    public function getSsoCookie(ParameterBag $cookies)
    {
        return $cookies->get(self::SSO_SESSION_COOKIE_NAME);
    }

    /**
     * Sets the SSO session cookie with the current Session ID
     */
    public function setSsoSessionCookie(ParameterBag $cookies, string $issuer): void
    {
        $result = $this->cookieService->setcookie(
            self::SSO_SESSION_COOKIE_NAME,
            $this->createSsoSessionCookie($cookies, $issuer),
            $this->maxAge === 0 ? $this->maxAge : time() + $this->maxAge,
            $this->cookiePath,
            $this->cookieDomain
        );
        if (!$result) {
            $this->logger->error("Failed to set SSO Session cookie");
        }
    }

    /**
     * Clears the SSO session cookie
     */
    public function clearSsoSessionCookie(): void
    {
        $result = $this->cookieService->clearCookie(
            self::SSO_SESSION_COOKIE_NAME,
            $this->cookiePath,
            $this->cookieDomain
        );
        if (!$result) {
            $this->logger->error("Failed to clear SSO Session cookie");
        }
    }

    private function createSsoSessionCookie(ParameterBag $cookies, string $issuer): string
    {
        $ssoSession = json_decode($this->getSsoCookie($cookies));

        if (!is_array($ssoSession)) {
            $ssoSession = [];
        }

        if (!in_array($issuer, $ssoSession)) {
            $ssoSession[] = $issuer;
        }

        return json_encode($ssoSession);
    }
}
