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

namespace OpenConext\EngineBlockBundle\Controller;

use InvalidArgumentException;
use OpenConext\EngineBlock\Service\Wayf\RememberedIdpCookie;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ResetRememberedWayfController
{
    private const ALLOWED_REDIRECT_SCHEMES = ['http', 'https'];

    public function __construct(
        private readonly RememberedIdpCookie $rememberedIdpCookie,
        private readonly LoggerInterface $logger,
        private readonly string $redirectUrl,
        private readonly array $allowedRedirectHosts = [],
    ) {
        if ($this->redirectUrl === '') {
            throw new InvalidArgumentException(
                'The "wayf.reset_choice_per_idp_redirect" parameter must be configured with a redirect URL'
            );
        }
    }

    #[Route(path: '/reset-remember-wayf', name: 'reset_remember_wayf', methods: ['GET'])]
    public function __invoke(Request $request): RedirectResponse
    {
        $raw = $request->cookies->get(RememberedIdpCookie::NAME);

        if ($raw !== null) {
            $entryCount = count($this->rememberedIdpCookie->normalize($raw)['entries']);
            $this->rememberedIdpCookie->clear();
            $this->logger->info(sprintf(
                'WAYF-remember-my-choice cookie removed (had %d entries)',
                $entryCount
            ));
        }

        $target = $this->resolveRedirectTarget($request->query->get('redirect'));
        $location = $this->appendQueryParameter($target, 'wayfReset', $raw !== null ? 'removed' : 'none');

        return new RedirectResponse($location, Response::HTTP_FOUND);
    }

    private function resolveRedirectTarget(?string $redirect): string
    {
        if ($redirect === null || $redirect === '') {
            return $this->redirectUrl;
        }

        $parts = parse_url($redirect);

        if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
            return $this->redirectUrl;
        }

        if (!in_array($parts['scheme'], self::ALLOWED_REDIRECT_SCHEMES, true)) {
            return $this->redirectUrl;
        }

        if (!in_array($parts['host'], $this->allowedRedirectHosts, true)) {
            return $this->redirectUrl;
        }

        return $redirect;
    }

    private function appendQueryParameter(string $url, string $key, string $value): string
    {
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . $key . '=' . rawurlencode($value);
    }
}
