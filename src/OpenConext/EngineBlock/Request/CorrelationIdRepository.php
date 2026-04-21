<?php

/**
 * Copyright 2010 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Request;

use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Session-only CRUD for correlation IDs.
 *
 * Stores and retrieves raw correlation ID strings keyed by SAML request ID.
 * Has no knowledge of which correlation ID is currently "active" — that is
 * the responsibility of CorrelationIdService + CurrentCorrelationId.
 */
final class CorrelationIdRepository
{
    private const SESSION_KEY = 'CorrelationIds';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /**
     * Persists a correlation ID for the given request ID.
     * No-op when no session is available.
     *
     * Note: this method unconditionally overwrites any existing entry.
     * Callers (e.g. CorrelationIdService::mint()) are responsible for the
     * back-button idempotency guard: call find() first and skip store() if
     * a value already exists.
     */
    public function store(string $requestId, CorrelationId $correlationId): void
    {
        try {
            $session = $this->requestStack->getSession();
        } catch (SessionNotFoundException) {
            return;
        }

        $ids = $session->get(self::SESSION_KEY, []);
        $ids[$requestId] = $correlationId->correlationId;
        $session->set(self::SESSION_KEY, $ids);
    }

    /**
     * Copies the correlation ID from $sourceRequestId to $targetRequestId.
     * No-op when source is not found or no session is available.
     */
    public function link(string $targetRequestId, string $sourceRequestId): void
    {
        try {
            $session = $this->requestStack->getSession();
        } catch (SessionNotFoundException) {
            return;
        }

        $ids = $session->get(self::SESSION_KEY, []);

        if (!array_key_exists($sourceRequestId, $ids)) {
            return;
        }

        $ids[$targetRequestId] = $ids[$sourceRequestId];
        $session->set(self::SESSION_KEY, $ids);
    }

    /**
     * Returns the CorrelationId for $requestId, or null when not found.
     * Returns null when no session is available.
     */
    public function find(string $requestId): ?CorrelationId
    {
        try {
            $session = $this->requestStack->getSession();
        } catch (SessionNotFoundException) {
            return null;
        }

        $value = $session->get(self::SESSION_KEY, [])[$requestId] ?? null;

        return $value !== null ? new CorrelationId($value) : null;
    }
}
