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

use Phake;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Tests\Logger;

class SsoSessionServiceTest extends TestCase
{
    private const SSO_SESSION_COOKIE_NAME = "sso_id";
    private const SSO_SESSION_COOKIE_MAX_AGE = 0;
    private const SSO_SESSION_COOKIE_DOMAIN = "test.domain";
    private const SSO_SESSION_COOKIE_PATH = "/testpath";

    /**
     * @var SsoSessionService
     */
    private $ssoQueryService;

    /**
     * @var CookieService
     */
    private $cookieServiceMock;

    public function setUp(): void
    {
        $this->cookieServiceMock = Phake::mock(CookieService::class);
        $loggerMock = Phake::mock(Logger::class);
        $this->ssoQueryService = new SsoSessionService(
            self::SSO_SESSION_COOKIE_MAX_AGE,
            self::SSO_SESSION_COOKIE_DOMAIN,
            self::SSO_SESSION_COOKIE_PATH,
            $this->cookieServiceMock,
            $loggerMock
        );
    }

    /**
     * @test
     * @group SsoSessionCookie
     */
    public function test_set_sso_session_cookie()
    {
        $cookieMock = Phake::mock(ParameterBag::class);
        Phake::when($cookieMock)
            ->get(Phake::equalTo(self::SSO_SESSION_COOKIE_NAME))
            ->thenReturn(null);

        $this->ssoQueryService->setSsoSessionCookie($cookieMock, "entityId");
        Phake::verify($this->cookieServiceMock)->setCookie(
            Phake::equalTo(self::SSO_SESSION_COOKIE_NAME),
            Phake::equalTo(json_encode(["entityId"])),
            Phake::equalTo(self::SSO_SESSION_COOKIE_MAX_AGE),
            Phake::equalTo(self::SSO_SESSION_COOKIE_PATH),
            Phake::equalTo(self::SSO_SESSION_COOKIE_DOMAIN)
        );
    }

    /**
     * @test
     * @group SsoSessionCookie
     */
    public function test_set_sso_session_cookie_invalid_cookie_resets()
    {
        $cookieMock = Phake::mock(ParameterBag::class);
        Phake::when($cookieMock)
            ->get(Phake::equalTo(self::SSO_SESSION_COOKIE_NAME))
            ->thenReturn("this is not something we would expect");

        $this->ssoQueryService->setSsoSessionCookie($cookieMock, "entityId");
        Phake::verify($this->cookieServiceMock)->setCookie(
            Phake::equalTo(self::SSO_SESSION_COOKIE_NAME),
            Phake::equalTo(json_encode(["entityId"])),
            Phake::equalTo(self::SSO_SESSION_COOKIE_MAX_AGE),
            Phake::equalTo(self::SSO_SESSION_COOKIE_PATH),
            Phake::equalTo(self::SSO_SESSION_COOKIE_DOMAIN)
        );
    }

    /**
     * @test
     * @group SsoSessionCookie
     */
    public function test_set_multiple_sso_session_cookies()
    {
        $cookieMock = Phake::mock(ParameterBag::class);
        Phake::when($cookieMock)
            ->get(Phake::equalTo(self::SSO_SESSION_COOKIE_NAME))
            ->thenReturn(json_encode(["entityId2"]));

        $this->ssoQueryService->setSsoSessionCookie($cookieMock, "entityId");
        Phake::verify($this->cookieServiceMock)->setCookie(
            Phake::equalTo(self::SSO_SESSION_COOKIE_NAME),
            Phake::equalTo(json_encode(["entityId2", "entityId"])),
            Phake::equalTo(self::SSO_SESSION_COOKIE_MAX_AGE),
            Phake::equalTo(self::SSO_SESSION_COOKIE_PATH),
            Phake::equalTo(self::SSO_SESSION_COOKIE_DOMAIN)
        );
    }

    /**
     * @test
     * @group SsoSessionCookie
     */
    public function test_set_multiple_sso_session_cookies_no_duplicates()
    {
        $cookieMock = Phake::mock(ParameterBag::class);
        Phake::when($cookieMock)
            ->get(Phake::equalTo(self::SSO_SESSION_COOKIE_NAME))
            ->thenReturn(json_encode(["entityId"]));

        $this->ssoQueryService->setSsoSessionCookie($cookieMock, "entityId");
        Phake::verify($this->cookieServiceMock)->setCookie(
            Phake::equalTo(self::SSO_SESSION_COOKIE_NAME),
            Phake::equalTo(json_encode(["entityId"])),
            Phake::equalTo(self::SSO_SESSION_COOKIE_MAX_AGE),
            Phake::equalTo(self::SSO_SESSION_COOKIE_PATH),
            Phake::equalTo(self::SSO_SESSION_COOKIE_DOMAIN)
        );
    }

    /**
     * @test
     * @group SsoSessionCookie
     */
    public function test_remove_sso_session_cookie()
    {
        $this->ssoQueryService->clearSsoSessionCookie();
        Phake::verify($this->cookieServiceMock)->clearCookie(
            Phake::equalTo(self::SSO_SESSION_COOKIE_NAME),
            Phake::equalTo(self::SSO_SESSION_COOKIE_PATH),
            Phake::equalTo(self::SSO_SESSION_COOKIE_DOMAIN)
        );
    }
}
