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

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Request\CurrentCorrelationId;
use OpenConext\EngineBlock\Service\FeedbackStateHelperInterface;
use OpenConext\EngineBlockBundle\Controller\FeedbackController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class FeedbackControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[Test]
    public function correlation_id_is_added_to_feedback_session_when_set(): void
    {
        $currentCorrelationId = new CurrentCorrelationId();
        $currentCorrelationId->correlationId = 'test-correlation-id-abc123';

        $feedbackStateHelper = Mockery::mock(FeedbackStateHelperInterface::class);
        $feedbackStateHelper->shouldReceive('mergeFeedbackInfo')
            ->once()
            ->with(Mockery::on(fn(array $info) => isset($info['correlationId']) && $info['correlationId'] === 'test-correlation-id-abc123'));

        $controller = $this->buildController($currentCorrelationId, $feedbackStateHelper);

        $controller->unknownServiceProviderAction($this->buildRequestWithSession());
    }

    #[Test]
    public function correlation_id_is_not_added_to_feedback_session_when_null(): void
    {
        $currentCorrelationId = new CurrentCorrelationId();
        $currentCorrelationId->correlationId = null;

        $feedbackStateHelper = Mockery::mock(FeedbackStateHelperInterface::class);
        $feedbackStateHelper->shouldReceive('mergeFeedbackInfo')
            ->once()
            ->with(Mockery::on(fn(array $info) => !array_key_exists('correlationId', $info)));

        $controller = $this->buildController($currentCorrelationId, $feedbackStateHelper);

        $controller->unknownServiceProviderAction($this->buildRequestWithSession());
    }

    private function buildController(CurrentCorrelationId $currentCorrelationId, FeedbackStateHelperInterface $feedbackStateHelper): FeedbackController
    {
        $translator = Mockery::mock(TranslatorInterface::class);
        $twig = Mockery::mock(Environment::class);
        $twig->allows('render')->andReturn('<html></html>');

        return new FeedbackController($translator, $twig, $feedbackStateHelper, $currentCorrelationId);
    }

    private function buildRequestWithSession(): Request
    {
        $request = Request::create('/authentication/feedback/unknown-service-provider?entity-id=https://sp.example.com');
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        return $request;
    }
}
