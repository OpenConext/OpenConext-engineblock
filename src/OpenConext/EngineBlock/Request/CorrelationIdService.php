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

/**
 * Orchestrates correlation ID operations across the four SAML legs.
 *
 *  mint()    — generate a new ID for a SAML request (back-button safe)
 *  link()    — copy an existing ID to a new SAML request ID (SP→IdP handoff)
 *  resolve() — look up the ID and push it into CurrentCorrelationId so all
 *              subsequent log entries carry it
 *
 * This is the single entry point used by Corto services. The session
 * interaction is delegated to CorrelationIdRepository.
 */
final class CorrelationIdService
{
    public function __construct(
        private readonly CorrelationIdRepository $repository,
        private readonly CurrentCorrelationId $current,
    ) {
    }

    /**
     * Generates and stores a correlation ID for $requestId if none exists yet.
     * Safe to call multiple times for the same ID (back-button guard).
     */
    public function mint(string $requestId): void
    {
        if ($this->repository->find($requestId) === null) {
            $this->repository->store($requestId, CorrelationId::mint());
        }
    }

    /**
     * Copies the correlation ID from $sourceRequestId to $targetRequestId.
     */
    public function link(string $targetRequestId, string $sourceRequestId): void
    {
        $this->repository->link($targetRequestId, $sourceRequestId);
    }

    /**
     * Looks up the correlation ID for $requestId and sets it as the active ID
     * in CurrentCorrelationId so all subsequent log entries carry it.
     * No-op when $requestId is null or not found.
     */
    public function resolve(?string $requestId): void
    {
        if ($requestId === null) {
            return;
        }

        $cid = $this->repository->find($requestId);

        if ($cid !== null) {
            $this->current->set($cid->correlationId);
        }
    }
}
