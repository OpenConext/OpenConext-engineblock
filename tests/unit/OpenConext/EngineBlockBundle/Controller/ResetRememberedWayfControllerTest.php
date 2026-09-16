<?php

/**
 * Copyright 2026 SURFnet B.V.
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

declare(strict_types=1);

namespace OpenConext\EngineBlockBundle\Tests;

use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Service\CookieService;
use OpenConext\EngineBlock\Service\TimeProvider\TimeProvider;
use OpenConext\EngineBlock\Service\Wayf\RememberedIdpCookie;
use OpenConext\EngineBlockBundle\Controller\ResetRememberedWayfController;
use Phake;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ResetRememberedWayfControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const string REDIRECT_URL = 'https://sp.example.org/after-reset';
    private const int LIFETIME = 7776000;
    private const int MAX_ENTRIES = 16;
    private const string COOKIE_DOMAIN = 'engine.example.org';
    private const string COOKIE_PATH = '/';
    private const array ALLOWED_REDIRECT_HOSTS = ['profile.example.org'];

    #[Test]
    public function cookie_with_valid_entries_is_cleared_and_logged_with_entry_count(): void
    {
        $cookieService = Phake::mock(CookieService::class);
        Phake::when($cookieService)->clearCookieWithSameSite(Phake::anyParameters())->thenReturn(true);

        $rememberedIdpCookie = $this->buildRememberedIdpCookie($cookieService);
        $raw = $rememberedIdpCookie->encode([
            'https://sp1.example.org' => ['idp' => 'https://idp1.example.org', 'expires' => time() + 3600],
            'https://sp2.example.org' => ['idp' => 'https://idp2.example.org', 'expires' => time() + 3600],
        ]);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')
            ->once()
            ->with('WAYF-remember-my-choice cookie removed (had 2 entries)');

        $controller = new ResetRememberedWayfController($rememberedIdpCookie, $logger, self::REDIRECT_URL);

        $response = $controller($this->buildRequest($raw));

        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame(self::REDIRECT_URL . '?wayfReset=removed', $response->getTargetUrl());
        Phake::verify($cookieService)->clearCookieWithSameSite(
            RememberedIdpCookie::NAME,
            self::COOKIE_PATH,
            self::COOKIE_DOMAIN,
            true,
            true,
            'None'
        );
    }

    #[Test]
    public function invalid_cookie_is_still_cleared_and_logged_with_zero_entries(): void
    {
        $cookieService = Phake::mock(CookieService::class);
        Phake::when($cookieService)->clearCookieWithSameSite(Phake::anyParameters())->thenReturn(true);

        $rememberedIdpCookie = $this->buildRememberedIdpCookie($cookieService);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')
            ->once()
            ->with('WAYF-remember-my-choice cookie removed (had 0 entries)');

        $controller = new ResetRememberedWayfController($rememberedIdpCookie, $logger, self::REDIRECT_URL);

        $response = $controller($this->buildRequest('not valid base64 or deflated data!'));

        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame(self::REDIRECT_URL . '?wayfReset=removed', $response->getTargetUrl());
        Phake::verify($cookieService)->clearCookieWithSameSite(Phake::anyParameters());
    }

    #[Test]
    public function missing_cookie_is_not_cleared_or_logged_but_still_redirects(): void
    {
        $cookieService = Phake::mock(CookieService::class);
        $rememberedIdpCookie = $this->buildRememberedIdpCookie($cookieService);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldNotReceive('info');

        $controller = new ResetRememberedWayfController($rememberedIdpCookie, $logger, self::REDIRECT_URL);

        $response = $controller($this->buildRequest(null));

        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame(self::REDIRECT_URL . '?wayfReset=none', $response->getTargetUrl());
        Phake::verifyNoInteraction($cookieService);
    }

    #[Test]
    public function constructor_rejects_an_empty_redirect_url(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ResetRememberedWayfController(
            $this->buildRememberedIdpCookie(Phake::mock(CookieService::class)),
            Mockery::mock(LoggerInterface::class),
            ''
        );
    }

    #[Test]
    public function a_redirect_parameter_pointing_at_an_allowed_host_is_honoured(): void
    {
        $cookieService = Phake::mock(CookieService::class);
        $rememberedIdpCookie = $this->buildRememberedIdpCookie($cookieService);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldNotReceive('info');

        $controller = new ResetRememberedWayfController(
            $rememberedIdpCookie,
            $logger,
            self::REDIRECT_URL,
            self::ALLOWED_REDIRECT_HOSTS,
        );

        $response = $controller($this->buildRequest(null, 'https://profile.example.org/my-profile'));

        $this->assertSame(
            'https://profile.example.org/my-profile?wayfReset=none',
            $response->getTargetUrl()
        );
    }

    #[Test]
    public function a_redirect_parameter_that_already_has_a_query_string_is_appended_to(): void
    {
        $cookieService = Phake::mock(CookieService::class);
        $rememberedIdpCookie = $this->buildRememberedIdpCookie($cookieService);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldNotReceive('info');

        $controller = new ResetRememberedWayfController(
            $rememberedIdpCookie,
            $logger,
            self::REDIRECT_URL,
            self::ALLOWED_REDIRECT_HOSTS,
        );

        $response = $controller($this->buildRequest(null, 'https://profile.example.org/my-profile?foo=bar'));

        $this->assertSame(
            'https://profile.example.org/my-profile?foo=bar&wayfReset=none',
            $response->getTargetUrl()
        );
    }

    #[Test]
    public function a_redirect_parameter_pointing_at_a_host_that_is_not_allowed_falls_back_to_the_configured_redirect(): void
    {
        $cookieService = Phake::mock(CookieService::class);
        $rememberedIdpCookie = $this->buildRememberedIdpCookie($cookieService);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldNotReceive('info');

        $controller = new ResetRememberedWayfController(
            $rememberedIdpCookie,
            $logger,
            self::REDIRECT_URL,
            self::ALLOWED_REDIRECT_HOSTS,
        );

        $response = $controller($this->buildRequest(null, 'https://evil.example.org/phishing'));

        $this->assertSame(self::REDIRECT_URL . '?wayfReset=none', $response->getTargetUrl());
    }

    #[Test]
    public function a_malformed_redirect_parameter_falls_back_to_the_configured_redirect(): void
    {
        $cookieService = Phake::mock(CookieService::class);
        $rememberedIdpCookie = $this->buildRememberedIdpCookie($cookieService);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldNotReceive('info');

        $controller = new ResetRememberedWayfController(
            $rememberedIdpCookie,
            $logger,
            self::REDIRECT_URL,
            self::ALLOWED_REDIRECT_HOSTS,
        );

        $response = $controller($this->buildRequest(null, 'not-a-url'));

        $this->assertSame(self::REDIRECT_URL . '?wayfReset=none', $response->getTargetUrl());
    }

    #[Test]
    public function a_redirect_parameter_with_a_disallowed_scheme_falls_back_to_the_configured_redirect(): void
    {
        $cookieService = Phake::mock(CookieService::class);
        $rememberedIdpCookie = $this->buildRememberedIdpCookie($cookieService);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldNotReceive('info');

        $controller = new ResetRememberedWayfController(
            $rememberedIdpCookie,
            $logger,
            self::REDIRECT_URL,
            self::ALLOWED_REDIRECT_HOSTS,
        );

        $response = $controller($this->buildRequest(null, 'javascript:alert(1)//profile.example.org'));

        $this->assertSame(self::REDIRECT_URL . '?wayfReset=none', $response->getTargetUrl());
    }

    private function buildRememberedIdpCookie(CookieService $cookieService): RememberedIdpCookie
    {
        return new RememberedIdpCookie(
            new TimeProvider(),
            $cookieService,
            self::LIFETIME,
            self::MAX_ENTRIES,
            self::COOKIE_DOMAIN,
            self::COOKIE_PATH,
            true,
        );
    }

    private function buildRequest(?string $rememberedIdpsCookie, ?string $redirect = null): Request
    {
        $query = $redirect !== null ? ['redirect' => $redirect] : [];
        $request = Request::create('/reset-remember-wayf', 'GET', $query);
        if ($rememberedIdpsCookie !== null) {
            $request->cookies->set(RememberedIdpCookie::NAME, $rememberedIdpsCookie);
        }

        return $request;
    }
}
