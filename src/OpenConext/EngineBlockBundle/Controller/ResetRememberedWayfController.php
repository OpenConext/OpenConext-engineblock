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
    public function __construct(
        private readonly RememberedIdpCookie $rememberedIdpCookie,
        private readonly LoggerInterface $logger,
        private readonly string $redirectUrl,
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

        return new RedirectResponse($this->redirectUrl, Response::HTTP_FOUND);
    }
}
