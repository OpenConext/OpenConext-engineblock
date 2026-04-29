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

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Tracks details about the various operations a user performs throughout the SAML flows
 */
class FeedbackStateHelper implements FeedbackStateHelperInterface
{
    private const EARLY_FEEDBACK_KEY = '_early';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    private function session(): SessionInterface
    {
        return $this->requestStack->getSession();
    }

    public function storeFeedbackInfo(array $feedback): void
    {
        $requestKey = $this->session()->get('currentSamlRequestId') ?? self::EARLY_FEEDBACK_KEY;
        $all = $this->session()->get('feedbackInfo') ?: [];
        $all[$requestKey] = $feedback;
        $this->session()->set('feedbackInfo', $all);
        $this->session()->set('currentFeedbackKey', $requestKey);
    }

    public function getFeedbackInfo(): array
    {
        $feedbackKey = $this->session()->get('currentFeedbackKey');
        if ($feedbackKey === null) {
            return [];
        }
        return ($this->session()->get('feedbackInfo') ?: [])[$feedbackKey] ?? [];
    }

    public function getActiveFlowContext(): array
    {
        $currentRequestId = $this->session()->get('currentSamlRequestId');
        if ($currentRequestId === null) {
            return [];
        }
        return ($this->session()->get('feedbackInfo') ?: [])[$currentRequestId] ?? [];
    }

    public function mergeFeedbackInfo(array $defaults): void
    {
        $feedbackKey = $this->session()->get('currentFeedbackKey') ?? self::EARLY_FEEDBACK_KEY;
        $all = $this->session()->get('feedbackInfo') ?: [];
        $all[$feedbackKey] = array_merge($defaults, $all[$feedbackKey] ?? []);
        $this->session()->set('feedbackInfo', $all);
    }

    public function clearFeedbackInfo(): void
    {
        $currentRequestId = $this->session()->get('currentSamlRequestId');
        if ($currentRequestId !== null) {
            $all = $this->session()->get('feedbackInfo') ?: [];
            unset($all[$currentRequestId]);
            if (empty($all)) {
                $this->session()->remove('feedbackInfo');
            } else {
                $this->session()->set('feedbackInfo', $all);
            }
        }
        $this->session()->remove('currentSamlRequestId');
        // currentFeedbackKey is intentionally preserved so error pages remain accessible after a subsequent successful login.
    }

    public function startNewFlow(string $samlRequestId, string $serviceProviderId): void
    {
        $this->session()->set('currentSamlRequestId', $samlRequestId);
        $all = $this->session()->get('feedbackInfo') ?: [];
        $all[$samlRequestId] = ['serviceProvider' => $serviceProviderId];
        $this->session()->set('feedbackInfo', $all);
    }

    public function setCurrentIdentityProvider(string $idpEntityId): void
    {
        $currentRequestId = $this->session()->get('currentSamlRequestId');
        if ($currentRequestId === null) {
            return;
        }
        $all = $this->session()->get('feedbackInfo') ?: [];
        $all[$currentRequestId]['identityProvider'] = $idpEntityId;
        $this->session()->set('feedbackInfo', $all);
    }

    public function setProxyContext(string $originalSpId, string $proxySpId): void
    {
        $currentRequestId = $this->session()->get('currentSamlRequestId');
        if ($currentRequestId === null) {
            return;
        }
        $all = $this->session()->get('feedbackInfo') ?: [];
        $all[$currentRequestId]['originalServiceProvider'] = $originalSpId;
        $all[$currentRequestId]['proxyServiceProvider'] = $proxySpId;
        $this->session()->set('feedbackInfo', $all);
    }

    public function clearFlowContext(): void
    {
        $this->clearFeedbackInfo();
    }
}
