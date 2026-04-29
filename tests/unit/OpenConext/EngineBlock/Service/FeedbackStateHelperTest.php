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

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class FeedbackStateHelperTest extends TestCase
{
    private Session $session;
    private FeedbackStateHelper $helper;

    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage());

        $request = new Request();
        $request->setSession($this->session);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $this->helper = new FeedbackStateHelper($requestStack);
    }

    #[Test]
    public function it_removes_only_the_current_flows_feedback_entry(): void
    {
        $this->session->set('feedbackInfo', ['req-1' => ['serviceProvider' => 'https://sp.example.com']]);
        $this->session->set('currentFeedbackKey', 'req-1');
        $this->session->set('currentSamlRequestId', 'req-1');

        $this->helper->clearFeedbackInfo();

        // The one entry was removed so feedbackInfo is now empty/gone
        self::assertNull($this->session->get('feedbackInfo'));
        // currentSamlRequestId is removed (the flow is done)
        self::assertNull($this->session->get('currentSamlRequestId'));
        // currentFeedbackKey is preserved so the error page URL still resolves
        self::assertSame('req-1', $this->session->get('currentFeedbackKey'));
    }

    #[Test]
    public function it_preserves_other_flows_feedback_info_when_clearing_current_flow(): void
    {
        $this->session->set('feedbackInfo', [
            'req-1' => ['serviceProvider' => 'https://failed-sp.example.com'],
            'req-2' => ['serviceProvider' => 'https://current-sp.example.com'],
        ]);
        $this->session->set('currentFeedbackKey', 'req-1');
        $this->session->set('currentSamlRequestId', 'req-2');

        $this->helper->clearFeedbackInfo();

        $remaining = $this->session->get('feedbackInfo');
        self::assertArrayHasKey('req-1', $remaining);
        self::assertArrayNotHasKey('req-2', $remaining);
        self::assertSame('req-1', $this->session->get('currentFeedbackKey'));
        self::assertNull($this->session->get('currentSamlRequestId'));
    }

    #[Test]
    public function it_is_a_no_op_when_no_feedback_info_is_in_the_session(): void
    {
        // Should not throw when keys are absent
        $this->helper->clearFeedbackInfo();

        self::assertNull($this->session->get('feedbackInfo'));
        self::assertNull($this->session->get('currentFeedbackKey'));
        self::assertNull($this->session->get('currentSamlRequestId'));
    }

    #[Test]
    public function it_stores_feedback_info_keyed_by_saml_request_id(): void
    {
        $this->session->set('currentSamlRequestId', 'req-xyz');

        $this->helper->storeFeedbackInfo(['serviceProvider' => 'https://sp.example.com']);

        $all = $this->session->get('feedbackInfo');
        self::assertArrayHasKey('req-xyz', $all);
        self::assertSame('https://sp.example.com', $all['req-xyz']['serviceProvider']);
        self::assertSame('req-xyz', $this->session->get('currentFeedbackKey'));
    }

    #[Test]
    public function it_stores_feedback_info_under_a_fallback_key_when_no_saml_request_id(): void
    {
        $feedback = ['serviceProvider' => 'https://sp.example.com'];
        $this->helper->storeFeedbackInfo($feedback);

        self::assertSame($feedback, $this->helper->getFeedbackInfo());
    }

    #[Test]
    public function it_returns_the_current_keyed_feedback_info(): void
    {
        $this->session->set('feedbackInfo', [
            'req-1' => ['serviceProvider' => 'https://sp.example.com'],
            'req-2' => ['serviceProvider' => 'https://other.example.com'],
        ]);
        $this->session->set('currentFeedbackKey', 'req-1');

        $result = $this->helper->getFeedbackInfo();

        self::assertSame(['serviceProvider' => 'https://sp.example.com'], $result);
    }

    #[Test]
    public function it_returns_empty_array_when_no_current_feedback_key(): void
    {
        $result = $this->helper->getFeedbackInfo();
        self::assertSame([], $result);
    }

    #[Test]
    public function it_returns_active_flow_context_by_saml_request_id(): void
    {
        $this->session->set('currentSamlRequestId', 'req-1');
        $this->session->set('feedbackInfo', [
            'req-1' => ['serviceProvider' => 'https://sp.example.com', 'identityProvider' => 'https://idp.example.com'],
            'req-2' => ['serviceProvider' => 'https://other.example.com'],
        ]);

        $result = $this->helper->getActiveFlowContext();

        self::assertSame('https://sp.example.com', $result['serviceProvider']);
        self::assertSame('https://idp.example.com', $result['identityProvider']);
    }

    #[Test]
    public function it_returns_empty_active_flow_context_when_no_saml_request_id(): void
    {
        self::assertSame([], $this->helper->getActiveFlowContext());
    }

    #[Test]
    public function it_starts_a_new_flow_setting_request_id_and_initialising_the_feedback_bucket(): void
    {
        $this->helper->startNewFlow('req-new', 'https://sp.example.com');

        self::assertSame('req-new', $this->session->get('currentSamlRequestId'));
        $bucket = ($this->session->get('feedbackInfo') ?: [])['req-new'] ?? null;
        self::assertSame('https://sp.example.com', $bucket['serviceProvider']);
        // No loose currentServiceProvider key is written
        self::assertNull($this->session->get('currentServiceProvider'));
    }

    #[Test]
    public function it_sets_the_current_identity_provider(): void
    {
        $this->session->set('currentSamlRequestId', 'req-1');
        $this->session->set('feedbackInfo', ['req-1' => ['serviceProvider' => 'https://sp.example.com']]);

        $this->helper->setCurrentIdentityProvider('https://idp.example.com');

        $bucket = ($this->session->get('feedbackInfo') ?: [])['req-1'] ?? [];
        self::assertSame('https://idp.example.com', $bucket['identityProvider']);
        // No loose currentIdentityProvider key is written
        self::assertNull($this->session->get('currentIdentityProvider'));
    }

    #[Test]
    public function it_sets_the_proxy_context_for_trusted_proxy_flows(): void
    {
        $this->session->set('currentSamlRequestId', 'req-1');
        $this->session->set('feedbackInfo', ['req-1' => ['serviceProvider' => 'https://sp.example.com']]);

        $this->helper->setProxyContext('https://realsp.example.com', 'https://proxy.example.com');

        $bucket = ($this->session->get('feedbackInfo') ?: [])['req-1'] ?? [];
        self::assertSame('https://realsp.example.com', $bucket['originalServiceProvider']);
        self::assertSame('https://proxy.example.com', $bucket['proxyServiceProvider']);
        // No loose session keys written
        self::assertNull($this->session->get('originalServiceProvider'));
        self::assertNull($this->session->get('proxyServiceProvider'));
    }

    #[Test]
    public function it_clears_flow_context_including_feedback_and_session_vars(): void
    {
        $this->session->set('feedbackInfo', ['req-1' => [
            'serviceProvider' => 'https://sp.example.com',
            'identityProvider' => 'https://idp.example.com',
            'originalServiceProvider' => 'https://orig-sp.example.com',
            'proxyServiceProvider' => 'https://proxy-sp.example.com',
        ]]);
        $this->session->set('currentFeedbackKey', 'req-1');
        $this->session->set('currentSamlRequestId', 'req-1');

        $this->helper->clearFlowContext();

        // feedbackInfo entry for the completed flow is removed (was the only entry)
        self::assertNull($this->session->get('feedbackInfo'));
        // currentFeedbackKey is preserved so error page URLs keep working
        self::assertSame('req-1', $this->session->get('currentFeedbackKey'));
        self::assertNull($this->session->get('currentSamlRequestId'));
    }
}
