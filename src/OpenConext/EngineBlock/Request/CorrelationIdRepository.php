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
 * Symfony service that owns all three correlation ID session operations:
 *
 *  mint()    — generate a new ID for a SAML request (back-button safe)
 *  link()    — copy an existing ID to a new SAML request ID (SP→IdP handoff)
 *  resolve() — look up the ID and push it into the CorrelationId value holder
 *
 * Uses the Symfony session bag under the key 'CorrelationIds'.
 * Registered as a shared service so it is instantiated once per HTTP request.
 *
 * Note: session entries are never explicitly evicted after a flow completes.
 * Each authentication adds up to two entries (SP + IdP request ID). This is
 * intentional and consistent with AuthnRequestSessionRepository's behaviour;
 * SAML sessions are short-lived so unbounded growth is not a concern in practice.
 */
final class CorrelationIdRepository
{
    private const SESSION_KEY = 'CorrelationIds';

    public function __construct(
        private readonly CorrelationId $correlationId,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * Generates and stores a correlation ID for $requestId if none exists yet.
     * Safe to call multiple times for the same ID (back-button guard).
     */
    public function mint(string $requestId): void
    {
        try {
            $session = $this->requestStack->getSession();
        } catch (SessionNotFoundException) {
            return;
        }

        $ids = $session->get(self::SESSION_KEY, []);

        if (!isset($ids[$requestId])) {
            $ids[$requestId] = bin2hex(random_bytes(16));
            $session->set(self::SESSION_KEY, $ids);
        }
    }

    /**
     * Copies the correlation ID from $sourceRequestId to $targetRequestId.
     * Called when EngineBlock creates its own AuthnRequest to send to the IdP,
     * so that the IdP leg can be traced back to the original SP flow.
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
     * Looks up the correlation ID for $requestId and pushes it into the
     * CorrelationId DI service so all subsequent log entries carry it.
     * No-op when $requestId is null or not found.
     */
    public function resolve(?string $requestId): void
    {
        if ($requestId === null) {
            return;
        }

        try {
            $session = $this->requestStack->getSession();
        } catch (SessionNotFoundException) {
            return;
        }

        $cid = $session->get(self::SESSION_KEY, [])[$requestId] ?? null;

        if ($cid !== null) {
            $this->correlationId->set($cid);
        }
    }
}
