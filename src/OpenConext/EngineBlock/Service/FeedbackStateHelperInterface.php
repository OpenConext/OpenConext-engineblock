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

namespace OpenConext\EngineBlock\Service;

/**
 * Manages the feedback (error context) state for an authentication flow.
 *
 * Feedback info is keyed by SAML request ID so that concurrent or sequential
 * flows do not bleed their error context into each other.
 */
interface FeedbackStateHelperInterface
{
    public function storeFeedbackInfo(array $feedback): void;

    public function getFeedbackInfo(): array;

    /**
     * Returns the feedback bucket for the actively running SAML flow (keyed by
     * currentSamlRequestId). Use this when you need the SP/IdP context that was
     * accumulated during the current flow (e.g. during error collection), before
     * storeFeedbackInfo() has been called for this flow.
     */
    public function getActiveFlowContext(): array;

    public function mergeFeedbackInfo(array $defaults): void;

    public function clearFeedbackInfo(): void;

    public function startNewFlow(string $samlRequestId, string $serviceProviderId): void;

    public function setCurrentIdentityProvider(string $idpEntityId): void;

    public function setProxyContext(string $originalSpId, string $proxySpId): void;

    public function clearFlowContext(): void;
}
