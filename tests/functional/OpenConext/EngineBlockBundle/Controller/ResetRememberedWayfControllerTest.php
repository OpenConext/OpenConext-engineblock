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

use OpenConext\EngineBlock\Service\Wayf\RememberedIdpCookie;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;

final class ResetRememberedWayfControllerTest extends FunctionalWebTestCase
{
    #[Test]
    public function visiting_the_endpoint_with_a_remembered_idp_cookie_clears_it_and_redirects(): void
    {
        $client = self::createClient();
        $client->getCookieJar()->set(new Cookie(
            RememberedIdpCookie::NAME,
            self::encodeEntries([
                'https://sp.example.org' => ['idp' => 'https://idp.example.org', 'expires' => time() + 3600],
            ]),
            null,
            '/',
            'engine.dev.openconext.local'
        ));

        $client->request('GET', 'https://engine.dev.openconext.local/reset-remember-wayf');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        // Asserting the exact configured redirect target (rather than merely "some Location
        // header") is deliberate: it's the only way this test would fail if the controller's
        // constructor validation ever throws (e.g. due to a blank redirect URL), since the
        // app's global exception listener also turns uncaught exceptions into a 302 elsewhere.
        $this->assertSame(
            self::getContainer()->getParameter('wayf.reset_choice_per_idp_redirect'),
            $response->headers->get('Location')
        );
    }

    #[Test]
    public function visiting_the_endpoint_without_a_remembered_idp_cookie_still_redirects(): void
    {
        $client = self::createClient();

        $client->request('GET', 'https://engine.dev.openconext.local/reset-remember-wayf');

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame(
            self::getContainer()->getParameter('wayf.reset_choice_per_idp_redirect'),
            $response->headers->get('Location')
        );
    }

    /** @param array<string, array{idp: string, expires: int}> $entries */
    private static function encodeEntries(array $entries): string
    {
        return base64_encode((string) gzdeflate((string) json_encode($entries)));
    }
}
