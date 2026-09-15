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

namespace OpenConext\EngineBlock\Service\Wayf;

use JsonException;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Service\CookieService;
use OpenConext\EngineBlock\Service\TimeProvider\TimeProvider;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class RememberedIdpCookie
{
    public const NAME = 'rememberedidps';

    /**
     * Whether the per-SP remember-choice mode is authoritative for the given SP: this
     * requires the global 'wayf.remember_choice' feature, the EngineBlock-wide per-SP
     * flag and the SP's own metadata coin to all be enabled.
     */
    public static function isEnabledForServiceProvider(
        bool $globalRememberChoiceEnabled,
        bool $rememberChoicePerIdpEnabled,
        ServiceProvider $serviceProvider,
    ): bool {
        return $globalRememberChoiceEnabled
            && $rememberChoicePerIdpEnabled
            && $serviceProvider->getCoins()->wayfRememberChoice() === true;
    }

    public function __construct(
        private readonly TimeProvider $timeProvider,
        private readonly CookieService $cookieService,
        private readonly int $lifetime,
        private readonly int $maxEntries,
        private readonly string $cookieDomain,
        private readonly string $cookiePath,
        private readonly bool $secure,
    ) {
    }

    /**
     * @return array{
     *     entries: array<string, array{idp: string, expires: int}>,
     *     invalid: bool,
     *     expired: int,
     *     changed: bool
     * }
     */
    public function normalize(?string $raw): array
    {
        if ($raw === null || $raw === '') {
            return $this->normalizedResult([], false, 0, false);
        }

        $decoded = base64_decode($raw, true);
        if ($decoded === false) {
            return $this->invalidResult();
        }

        $inflated = $this->inflate($decoded);
        if ($inflated === null) {
            return $this->invalidResult();
        }

        try {
            $entries = json_decode($inflated, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $this->invalidResult();
        }

        if (!is_array($entries)) {
            return $this->invalidResult();
        }

        return $this->normalizeEntries($entries);
    }

    /**
     * Normalizes the raw cookie value, clearing it when invalid (after logging a warning)
     * and rewriting or clearing it when normalization changed the entries, then returns
     * the resulting valid entries.
     *
     * @return array<string, array{idp: string, expires: int}>
     */
    public function loadValidEntries(?string $raw, LoggerInterface $logger): array
    {
        $normalized = $this->normalize($raw);

        if ($normalized['invalid']) {
            $logger->warning('Invalid rememberedidps cookie encountered, clearing it');
            $this->clear();

            return [];
        }

        if ($normalized['changed']) {
            if (count($normalized['entries']) > 0) {
                $this->write($normalized['entries']);
            } else {
                $this->clear();
            }
        }

        return $normalized['entries'];
    }

    /** @param array<string, array{idp: string, expires: int}> $entries */
    public function encode(array $entries): string
    {
        $json = json_encode($entries, JSON_THROW_ON_ERROR);
        $deflated = gzdeflate($json);
        if ($deflated === false) {
            throw new RuntimeException('Unable to deflate remembered IdP cookie data');
        }

        return base64_encode($deflated);
    }

    /**
     * @param array<string, array{idp: string, expires: int}> $entries
     * @param string[] $candidateIdpEntityIds
     */
    public function find(array $entries, string $serviceProviderEntityId, array $candidateIdpEntityIds): ?string
    {
        if (!isset($entries[$serviceProviderEntityId])) {
            return null;
        }

        $entry = $entries[$serviceProviderEntityId];
        if (!$this->isValidEntry($serviceProviderEntityId, $entry) || $this->isExpired($entry['expires'])) {
            return null;
        }

        if (!in_array($entry['idp'], $candidateIdpEntityIds, true)) {
            return null;
        }

        return $entry['idp'];
    }

    /**
     * @param array<string, array{idp: string, expires: int}> $entries
     * @return array{entries: array<string, array{idp: string, expires: int}>, expires: int, dropped: int}
     */
    public function add(array $entries, string $serviceProviderEntityId, string $identityProviderEntityId): array
    {
        $expires = $this->cookieExpiresAt();
        $entries = $this->onlyValidUnexpiredEntries($entries);
        $entries[$serviceProviderEntityId] = [
            'idp' => $identityProviderEntityId,
            'expires' => $expires,
        ];

        $dropped = $this->pruneOverLimit($entries);

        return [
            'entries' => $entries,
            'expires' => $expires,
            'dropped' => $dropped,
        ];
    }

    public function cookieExpiresAt(): int
    {
        return $this->timeProvider->time() + $this->lifetime;
    }

    /**
     * @param array<string, array{idp: string, expires: int}> $entries
     * @param int|null $expires Pass the expiry already computed by add() so the logged/returned
     *     value and the actual cookie expiry never drift apart. Defaults to a freshly computed
     *     expiry (e.g. when merely refreshing the cookie's lifetime after normalization).
     */
    public function write(array $entries, ?int $expires = null): bool
    {
        // SameSite=None requires the Secure attribute or browsers silently drop the cookie; this
        // is only safe because 'cookie.secure' defaults to (and, per SAML's HTTPS requirement,
        // should always remain) true.
        return $this->cookieService->setCookieWithSameSite(
            self::NAME,
            $this->encode($entries),
            $expires ?? $this->cookieExpiresAt(),
            $this->cookiePath,
            $this->cookieDomain,
            $this->secure,
            true,
            'None'
        );
    }

    public function clear(): bool
    {
        // See the note on write() about the Secure/SameSite=None coupling.
        return $this->cookieService->clearCookieWithSameSite(
            self::NAME,
            $this->cookiePath,
            $this->cookieDomain,
            $this->secure,
            true,
            'None'
        );
    }

    private function normalizeEntries(array $entries): array
    {
        $normalized = [];
        $expired = 0;

        foreach ($entries as $serviceProviderEntityId => $entry) {
            if (!$this->isValidEntry($serviceProviderEntityId, $entry)) {
                return $this->invalidResult();
            }

            if ($this->isExpired($entry['expires'])) {
                $expired++;
                continue;
            }

            $normalized[$serviceProviderEntityId] = $entry;
        }

        return $this->normalizedResult($normalized, false, $expired, $expired > 0);
    }

    private function inflate(string $decoded): ?string
    {
        set_error_handler(static fn(): bool => true);
        try {
            $inflated = gzinflate($decoded);
        } finally {
            restore_error_handler();
        }

        return $inflated === false ? null : $inflated;
    }

    private function invalidResult(): array
    {
        return $this->normalizedResult([], true, 0, true);
    }

    private function normalizedResult(array $entries, bool $invalid, int $expired, bool $changed): array
    {
        return [
            'entries' => $entries,
            'invalid' => $invalid,
            'expired' => $expired,
            'changed' => $changed,
        ];
    }

    private function isValidEntry(mixed $serviceProviderEntityId, mixed $entry): bool
    {
        if (!is_array($entry)) {
            return false;
        }

        $entryKeys = array_keys($entry);
        sort($entryKeys);

        return is_string($serviceProviderEntityId)
            && $serviceProviderEntityId !== ''
            && $entryKeys === ['expires', 'idp']
            && is_string($entry['idp'])
            && $entry['idp'] !== ''
            && is_int($entry['expires']);
    }

    private function isExpired(int $expires): bool
    {
        return $expires <= $this->timeProvider->time();
    }

    private function onlyValidUnexpiredEntries(array $entries): array
    {
        $validEntries = [];
        foreach ($entries as $serviceProviderEntityId => $entry) {
            if (!$this->isValidEntry($serviceProviderEntityId, $entry) || $this->isExpired($entry['expires'])) {
                continue;
            }

            $validEntries[$serviceProviderEntityId] = $entry;
        }

        return $validEntries;
    }

    private function pruneOverLimit(array &$entries): int
    {
        if (count($entries) <= $this->maxEntries) {
            return 0;
        }

        uksort($entries, function (string $left, string $right) use ($entries): int {
            $expiryComparison = $entries[$right]['expires'] <=> $entries[$left]['expires'];
            if ($expiryComparison !== 0) {
                return $expiryComparison;
            }

            return $left <=> $right;
        });

        $dropped = count($entries) - $this->maxEntries;
        $entries = array_slice($entries, 0, $this->maxEntries, true);

        return $dropped;
    }
}
