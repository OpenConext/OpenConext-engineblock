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

namespace Tests\OpenConext\EngineBlock\Service\Wayf;

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Service\CookieService;
use OpenConext\EngineBlock\Service\TimeProvider\TimeProvider;
use OpenConext\EngineBlock\Service\Wayf\RememberedIdpCookie;
use Phake;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RememberedIdpCookieTest extends TestCase
{
    public const int NOW = 1700000000;
    private const int LIFETIME = 7776000;
    private const int MAX_ENTRIES = 16;
    private const string COOKIE_DOMAIN = 'engine.example.org';
    private const string COOKIE_PATH = '/';

    #[DataProvider('isEnabledForServiceProviderProvider')]
    public function testIsEnabledForServiceProvider(
        bool $globalRememberChoiceEnabled,
        bool $rememberChoicePerIdpEnabled,
        bool $spWayfRememberChoice,
        bool $expected
    ): void {
        $serviceProvider = new ServiceProvider(
            'https://sp.example.org',
            wayfRememberChoice: $spWayfRememberChoice
        );

        $this->assertSame(
            $expected,
            RememberedIdpCookie::isEnabledForServiceProvider(
                $globalRememberChoiceEnabled,
                $rememberChoicePerIdpEnabled,
                $serviceProvider
            )
        );
    }

    public static function isEnabledForServiceProviderProvider(): array
    {
        return [
            'all enabled' => [true, true, true, true],
            'global disabled' => [false, true, true, false],
            'per-idp feature disabled' => [true, false, true, false],
            'sp opted out' => [true, true, false, false],
            'all disabled' => [false, false, false, false],
        ];
    }

    public function testValidEntryRoundTripsThroughCookieCodec(): void
    {
        $cookie = $this->buildCookie();

        /** @var array<string, array{idp: string, expires: int}> $entries */
        $entries = [
            'https://sp.example.org' => [
                'idp' => 'https://idp.example.org',
                'expires' => 1700003600,
            ],
        ];

        $encoded = $cookie->encode($entries);
        $result = $cookie->normalize($encoded);

        $this->assertNotSame(json_encode($entries), $encoded);
        $this->assertSame($entries, $result['entries']);
        $this->assertFalse($result['invalid']);
        $this->assertSame(0, $result['expired']);
        $this->assertFalse($result['changed']);
    }

    #[DataProvider('invalidCookieProvider')]
    public function testInvalidCookieInputReturnsInvalidResultWithoutThrowing(string $raw): void
    {
        try {
            $result = $this->buildCookie()->normalize($raw);
        } catch (\Throwable $exception) {
            $this->fail(sprintf('Invalid cookie input should not throw: %s', $exception->getMessage()));
        }

        $this->assertTrue($result['invalid']);
        $this->assertSame([], $result['entries']);
    }

    public static function invalidCookieProvider(): array
    {
        return [
            'invalid base64' => ['not valid base64!'],
            'invalid deflate data' => [base64_encode('not deflated')],
            'invalid JSON' => [self::rawJson('{not json')],
            'non-array root' => [self::rawJson('"not an array"')],
            'empty service provider entity id' => [self::rawJson('{"":{"idp":"https://idp.example.org","expires":1700003600}}')],
            'non-array entry' => [self::rawJson('{"https://sp.example.org":"not an entry"}')],
            'missing idp' => [self::rawJson('{"https://sp.example.org":{"expires":1700003600}}')],
            'non-string idp' => [self::rawJson('{"https://sp.example.org":{"idp":123,"expires":1700003600}}')],
            'empty idp' => [self::rawJson('{"https://sp.example.org":{"idp":"","expires":1700003600}}')],
            'missing expires' => [self::rawJson('{"https://sp.example.org":{"idp":"https://idp.example.org"}}')],
            'non-integer expires' => [self::rawJson('{"https://sp.example.org":{"idp":"https://idp.example.org","expires":"1700003600"}}')],
            'extra entry key' => [self::rawJson('{"https://sp.example.org":{"idp":"https://idp.example.org","expires":1700003600,"extra":"unexpected"}}')],
        ];
    }

    public function testExpiredEntriesAreRemovedWhenNormalizing(): void
    {
        $raw = self::rawEntries([
            'https://expired.example.org' => ['idp' => 'https://idp1.example.org', 'expires' => self::NOW - 1],
            'https://expires-now.example.org' => ['idp' => 'https://idp2.example.org', 'expires' => self::NOW],
            'https://valid.example.org' => ['idp' => 'https://idp3.example.org', 'expires' => self::NOW + 1],
        ]);

        $result = $this->buildCookie()->normalize($raw);

        $this->assertSame(
            ['https://valid.example.org' => ['idp' => 'https://idp3.example.org', 'expires' => self::NOW + 1]],
            $result['entries']
        );
        $this->assertFalse($result['invalid']);
        $this->assertSame(2, $result['expired']);
        $this->assertTrue($result['changed']);
    }

    public function testOnlyAddPrunesOverLimitEntriesByNewestExpiry(): void
    {
        $cookie = $this->buildCookie();
        $entries = $this->validEntries(17);

        $normalized = $cookie->normalize(self::rawEntries($entries));
        $this->assertCount(17, $normalized['entries']);
        $this->assertFalse($normalized['changed']);

        $result = $cookie->add($entries, 'https://sp-new.example.org', 'https://idp-new.example.org');

        $this->assertCount(self::MAX_ENTRIES, $result['entries']);
        $this->assertSame(2, $result['dropped']);
        $this->assertArrayNotHasKey('https://sp-01.example.org', $result['entries']);
        $this->assertArrayNotHasKey('https://sp-02.example.org', $result['entries']);
        $this->assertArrayHasKey('https://sp-17.example.org', $result['entries']);
        $this->assertSame(
            ['idp' => 'https://idp-new.example.org', 'expires' => self::NOW + self::LIFETIME],
            $result['entries']['https://sp-new.example.org']
        );
    }

    public function testFindOnlyReturnsUnexpiredCandidateForServiceProvider(): void
    {
        $cookie = $this->buildCookie();
        $entries = [
            'https://sp.example.org' => ['idp' => 'https://idp.example.org', 'expires' => self::NOW + 1],
            'https://expired.example.org' => ['idp' => 'https://expired-idp.example.org', 'expires' => self::NOW],
        ];

        $this->assertSame(
            'https://idp.example.org',
            $cookie->find($entries, 'https://sp.example.org', ['https://idp.example.org'])
        );
        $this->assertNull($cookie->find($entries, 'https://unknown-sp.example.org', ['https://idp.example.org']));
        $this->assertNull($cookie->find($entries, 'https://expired.example.org', ['https://expired-idp.example.org']));
        $this->assertNull($cookie->find($entries, 'https://sp.example.org', ['https://other-idp.example.org']));
    }

    public function testAddReplacesExistingSpEntryAndReportsDroppedEntries(): void
    {
        $cookie = $this->buildCookie(maxEntries: 2);
        $entries = [
            'https://sp.example.org' => ['idp' => 'https://old-idp.example.org', 'expires' => self::NOW + 10],
            'https://sp-keep.example.org' => ['idp' => 'https://idp-keep.example.org', 'expires' => self::NOW + 9],
            'https://sp-drop.example.org' => ['idp' => 'https://idp-drop.example.org', 'expires' => self::NOW + 1],
        ];

        $result = $cookie->add($entries, 'https://sp.example.org', 'https://new-idp.example.org');

        $this->assertSame(self::NOW + self::LIFETIME, $result['expires']);
        $this->assertSame(1, $result['dropped']);
        $this->assertSame(
            ['idp' => 'https://new-idp.example.org', 'expires' => self::NOW + self::LIFETIME],
            $result['entries']['https://sp.example.org']
        );
        $this->assertArrayHasKey('https://sp-keep.example.org', $result['entries']);
        $this->assertArrayNotHasKey('https://sp-drop.example.org', $result['entries']);
    }

    public function testEmptyNormalizedResultIndicatesCookieShouldBeCleared(): void
    {
        $result = $this->buildCookie()->normalize(self::rawEntries([
            'https://expired.example.org' => ['idp' => 'https://idp.example.org', 'expires' => self::NOW],
        ]));

        $this->assertSame([], $result['entries']);
        $this->assertFalse($result['invalid']);
        $this->assertSame(1, $result['expired']);
        $this->assertTrue($result['changed']);
    }

    public function testWriteAndClearUseSameSiteAwareCookieOperations(): void
    {
        $cookieService = Phake::mock(CookieService::class);
        Phake::when($cookieService)->setCookieWithSameSite(Phake::anyParameters())->thenReturn(true);
        Phake::when($cookieService)->clearCookieWithSameSite(Phake::anyParameters())->thenReturn(true);

        $cookie = $this->buildCookie(cookieService: $cookieService);
        $entries = [
            'https://sp.example.org' => ['idp' => 'https://idp.example.org', 'expires' => self::NOW + self::LIFETIME],
        ];
        $encoded = $cookie->encode($entries);

        $this->assertSame(self::NOW + self::LIFETIME, $cookie->cookieExpiresAt());
        $this->assertTrue($cookie->write($entries));
        $this->assertTrue($cookie->clear());

        Phake::verify($cookieService)->setCookieWithSameSite(
            RememberedIdpCookie::NAME,
            $encoded,
            self::NOW + self::LIFETIME,
            self::COOKIE_PATH,
            self::COOKIE_DOMAIN,
            true,
            true,
            'None'
        );
        Phake::verify($cookieService)->clearCookieWithSameSite(
            RememberedIdpCookie::NAME,
            self::COOKIE_PATH,
            self::COOKIE_DOMAIN,
            true,
            true,
            'None'
        );
    }

    public function testWriteUsesExplicitlyProvidedExpiryWhenGiven(): void
    {
        $cookieService = Phake::mock(CookieService::class);
        Phake::when($cookieService)->setCookieWithSameSite(Phake::anyParameters())->thenReturn(true);

        $cookie = $this->buildCookie(cookieService: $cookieService);
        $entries = [
            'https://sp.example.org' => ['idp' => 'https://idp.example.org', 'expires' => self::NOW + self::LIFETIME],
        ];
        $explicitExpiry = self::NOW + self::LIFETIME + 1234;

        $this->assertTrue($cookie->write($entries, $explicitExpiry));

        Phake::verify($cookieService)->setCookieWithSameSite(
            RememberedIdpCookie::NAME,
            $cookie->encode($entries),
            $explicitExpiry,
            self::COOKIE_PATH,
            self::COOKIE_DOMAIN,
            true,
            true,
            'None'
        );
    }

    public function testLoadValidEntriesLogsWarningAndClearsCookieOnInvalidInput(): void
    {
        $cookieService = Phake::mock(CookieService::class);
        Phake::when($cookieService)->clearCookieWithSameSite(Phake::anyParameters())->thenReturn(true);
        $logger = Phake::mock(LoggerInterface::class);

        $cookie = $this->buildCookie(cookieService: $cookieService);

        $entries = $cookie->loadValidEntries('not valid base64!', $logger);

        $this->assertSame([], $entries);
        Phake::verify($logger)->warning('Invalid rememberedidps cookie encountered, clearing it');
        Phake::verify($cookieService)->clearCookieWithSameSite(
            RememberedIdpCookie::NAME,
            self::COOKIE_PATH,
            self::COOKIE_DOMAIN,
            true,
            true,
            'None'
        );
        Phake::verifyNoFurtherInteraction($cookieService);
    }

    public function testLoadValidEntriesWritesCookieWhenNormalizationChangedEntries(): void
    {
        $cookieService = Phake::mock(CookieService::class);
        Phake::when($cookieService)->setCookieWithSameSite(Phake::anyParameters())->thenReturn(true);
        $logger = Phake::mock(LoggerInterface::class);

        $cookie = $this->buildCookie(cookieService: $cookieService);
        $raw = self::rawEntries([
            'https://expired.example.org' => ['idp' => 'https://idp1.example.org', 'expires' => self::NOW - 1],
            'https://valid.example.org' => ['idp' => 'https://idp2.example.org', 'expires' => self::NOW + 1],
        ]);

        $entries = $cookie->loadValidEntries($raw, $logger);

        $this->assertSame(
            ['https://valid.example.org' => ['idp' => 'https://idp2.example.org', 'expires' => self::NOW + 1]],
            $entries
        );
        Phake::verify($cookieService)->setCookieWithSameSite(
            RememberedIdpCookie::NAME,
            $cookie->encode($entries),
            self::NOW + self::LIFETIME,
            self::COOKIE_PATH,
            self::COOKIE_DOMAIN,
            true,
            true,
            'None'
        );
        Phake::verifyNoInteraction($logger);
    }

    public function testLoadValidEntriesClearsCookieWhenNormalizationChangedEntriesToEmpty(): void
    {
        $cookieService = Phake::mock(CookieService::class);
        Phake::when($cookieService)->clearCookieWithSameSite(Phake::anyParameters())->thenReturn(true);
        $logger = Phake::mock(LoggerInterface::class);

        $cookie = $this->buildCookie(cookieService: $cookieService);
        $raw = self::rawEntries([
            'https://expired.example.org' => ['idp' => 'https://idp1.example.org', 'expires' => self::NOW],
        ]);

        $entries = $cookie->loadValidEntries($raw, $logger);

        $this->assertSame([], $entries);
        Phake::verify($cookieService)->clearCookieWithSameSite(
            RememberedIdpCookie::NAME,
            self::COOKIE_PATH,
            self::COOKIE_DOMAIN,
            true,
            true,
            'None'
        );
        Phake::verifyNoInteraction($logger);
    }

    public function testLoadValidEntriesDoesNotWriteOrClearWhenUnchanged(): void
    {
        $cookieService = Phake::mock(CookieService::class);
        $logger = Phake::mock(LoggerInterface::class);

        $cookie = $this->buildCookie(cookieService: $cookieService);
        $entries = [
            'https://sp.example.org' => ['idp' => 'https://idp.example.org', 'expires' => self::NOW + 1],
        ];
        $raw = self::rawEntries($entries);

        $result = $cookie->loadValidEntries($raw, $logger);

        $this->assertSame($entries, $result);
        Phake::verifyNoInteraction($cookieService);
        Phake::verifyNoInteraction($logger);
    }

    private function buildCookie(?CookieService $cookieService = null, int $maxEntries = self::MAX_ENTRIES): RememberedIdpCookie
    {
        return new RememberedIdpCookie(
            $this->fixedTimeProvider(),
            $cookieService ?? Phake::mock(CookieService::class),
            self::LIFETIME,
            $maxEntries,
            self::COOKIE_DOMAIN,
            self::COOKIE_PATH,
            true,
        );
    }

    private function fixedTimeProvider(): TimeProvider
    {
        return new class extends TimeProvider {
            public function time()
            {
                return RememberedIdpCookieTest::NOW;
            }
        };
    }

    private static function rawEntries(array $entries): string
    {
        return self::rawJson((string) json_encode($entries));
    }

    private static function rawJson(string $json): string
    {
        return base64_encode((string) gzdeflate($json));
    }

    private function validEntries(int $count): array
    {
        $entries = [];
        for ($i = 1; $i <= $count; $i++) {
            $entries[sprintf('https://sp-%02d.example.org', $i)] = [
                'idp' => sprintf('https://idp-%02d.example.org', $i),
                'expires' => self::NOW + $i,
            ];
        }

        return $entries;
    }
}
