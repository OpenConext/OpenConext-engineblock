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

namespace OpenConext\EngineBlockBundle\Tests;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;

final class FeedbackControllerTest extends FunctionalWebTestCase
{
    #[Test]
    public function session_lost_returns_400_with_expected_content(): void
    {
        $this->assertFeedbackPage('/authentication/feedback/session-lost', Response::HTTP_BAD_REQUEST, 'your session was lost');
    }

    #[Test]
    public function session_not_started_returns_400_with_expected_content(): void
    {
        $this->assertFeedbackPage('/authentication/feedback/session-not-started', Response::HTTP_BAD_REQUEST, 'No session found');
    }

    #[Test]
    public function unsolicited_response_returns_400_with_expected_content(): void
    {
        $this->assertFeedbackPage('/authentication/feedback/unsolicited-response', Response::HTTP_BAD_REQUEST, 'Sign-in could not be completed');
    }

    #[Test]
    public function invalid_acs_binding_returns_400_with_expected_content(): void
    {
        $this->assertFeedbackPage('/authentication/feedback/invalid-acs-binding', Response::HTTP_BAD_REQUEST, 'Invalid ACS binding type');
    }

    #[Test]
    public function received_error_status_code_returns_400_with_expected_content(): void
    {
        $this->assertFeedbackPage('/authentication/feedback/received-error-status-code', Response::HTTP_BAD_REQUEST, 'Identity Provider error');
    }

    #[Test]
    public function unable_to_receive_message_returns_400_with_expected_content(): void
    {
        $this->assertFeedbackPage('/authentication/feedback/unable-to-receive-message', Response::HTTP_BAD_REQUEST, 'No message received');
    }

    #[Test]
    public function unknown_requesterid_in_authnrequest_returns_400_with_expected_content(): void
    {
        $this->assertFeedbackPage('/authentication/feedback/unknown_requesterid_in_authnrequest', Response::HTTP_BAD_REQUEST, 'Unknown application');
    }

    #[Test]
    public function authentication_limit_exceeded_returns_429_with_expected_content(): void
    {
        $this->assertFeedbackPage('/authentication/feedback/authentication-limit-exceeded', Response::HTTP_TOO_MANY_REQUESTS, 'too many authentications in progress');
    }

    #[Test]
    public function stepup_callout_unknown_returns_400_with_expected_content(): void
    {
        $this->assertFeedbackPage('/authentication/feedback/stepup-callout-unknown', Response::HTTP_BAD_REQUEST, 'Unknown strong authentication failure');
    }

    #[Test]
    public function stepup_callout_user_cancelled_returns_400_with_expected_content(): void
    {
        $this->assertFeedbackPage('/authentication/feedback/stepup-callout-user-cancelled', Response::HTTP_BAD_REQUEST, 'Logging in cancelled');
    }

    #[Test]
    public function invalid_acs_location_returns_400_with_expected_content(): void
    {
        $this->assertFeedbackPage('/authentication/feedback/invalidAcsLocation', Response::HTTP_BAD_REQUEST, 'Invalid ACS location');
    }

    #[Test]
    public function feedback_data_from_session_is_rendered_on_the_real_route(): void
    {
        $client = self::createClient();

        // First prime, the session, then visit the actual route
        $client->request('GET', 'https://engine.dev.openconext.local/functional-testing/feedback?template=session-lost');
        $client->request('GET', 'https://engine.dev.openconext.local/authentication/feedback/session-lost');

        $content = $client->getResponse()->getContent();
        $this->assertStringContainsString('feedback-info--requestid', $content);
        $this->assertStringContainsString('feedback-info--ipaddress', $content);
    }

    private function assertFeedbackPage(string $path, int $expectedStatus, string $expectedPhrase): void
    {
        $client = self::createClient();
        $client->request('GET', 'https://engine.dev.openconext.local' . $path);

        $this->assertEquals($expectedStatus, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString($expectedPhrase, $client->getResponse()->getContent());
    }
}
