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
use OpenConext\EngineBlockBundle\Controller\FeedbackController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
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

        $controller = $this->buildController($currentCorrelationId);
        $request = $this->buildRequestWithSession();

        $controller->unknownServiceProviderAction($request);

        $feedbackInfo = $request->getSession()->get('feedbackInfo');
        $this->assertArrayHasKey('correlationId', $feedbackInfo);
        $this->assertSame('test-correlation-id-abc123', $feedbackInfo['correlationId']);
    }

    #[Test]
    public function correlation_id_is_not_added_to_feedback_session_when_null(): void
    {
        $currentCorrelationId = new CurrentCorrelationId();
        $currentCorrelationId->correlationId = null;

        $controller = $this->buildController($currentCorrelationId);
        $request = $this->buildRequestWithSession();

        $controller->unknownServiceProviderAction($request);

        $feedbackInfo = $request->getSession()->get('feedbackInfo');
        $this->assertArrayNotHasKey('correlationId', $feedbackInfo);
    }

    private function buildController(CurrentCorrelationId $currentCorrelationId): FeedbackController
    {
        $translator = Mockery::mock(TranslatorInterface::class);
        $twig = Mockery::mock(Environment::class);
        $twig->allows('render')->andReturn('<html></html>');
        $logger = Mockery::mock(LoggerInterface::class);

        return new FeedbackController($translator, $twig, $logger, $currentCorrelationId);
    }

    private function buildRequestWithSession(): Request
    {
        $request = Request::create('/authentication/feedback/unknown-service-provider?entity-id=https://sp.example.com');
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);

        return $request;
    }
}
