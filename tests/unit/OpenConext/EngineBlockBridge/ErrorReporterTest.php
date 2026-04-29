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

namespace OpenConext\EngineBlockBridge;

use EngineBlock_ApplicationSingleton;
use EngineBlock_Corto_Exception_HasFeedbackInfoInterface;
use EngineBlock_Corto_Exception_PEPNoAccess;
use Exception;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Service\FeedbackInfoCollectorInterface;
use OpenConext\EngineBlock\Service\FeedbackStateHelperInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class ErrorReporterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Session $session;
    private RequestStack $requestStack;
    private EngineBlock_ApplicationSingleton $applicationSingleton;
    private LoggerInterface $logger;
    private FeedbackStateHelperInterface $feedbackStateHelper;
    private FeedbackInfoCollectorInterface $feedbackInfoCollector;
    private ErrorReporter $reporter;

    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage());

        $request = new Request();
        $request->setSession($this->session);

        $this->requestStack = new RequestStack();
        $this->requestStack->push($request);

        $this->applicationSingleton = m::mock(EngineBlock_ApplicationSingleton::class);
        $this->applicationSingleton->shouldReceive('flushLog')->byDefault();

        $this->logger = m::mock(LoggerInterface::class);
        $this->logger->shouldIgnoreMissing();

        $this->feedbackStateHelper = m::mock(FeedbackStateHelperInterface::class);
        $this->feedbackStateHelper->shouldReceive('storeFeedbackInfo')->byDefault();

        $this->feedbackInfoCollector = m::mock(FeedbackInfoCollectorInterface::class);
        $this->feedbackInfoCollector->shouldReceive('collect')->andReturn([])->byDefault();

        $this->reporter = new ErrorReporter(
            $this->applicationSingleton,
            $this->logger,
            $this->requestStack,
            $this->feedbackStateHelper,
            $this->feedbackInfoCollector,
        );
    }

    #[Test]
    public function it_collects_and_stores_feedback_via_the_dedicated_services(): void
    {
        $exception = new Exception('test');
        $feedback = ['artCode' => 'art:0:0:0:0'];

        $this->feedbackInfoCollector->shouldReceive('collect')->with($exception)->once()->andReturn($feedback);
        $this->feedbackStateHelper->shouldReceive('storeFeedbackInfo')->with($feedback)->once();

        $this->reporter->reportError($exception, '');
    }

    #[Test]
    public function it_skips_feedback_storage_when_there_is_no_active_request(): void
    {
        $reporter = new ErrorReporter(
            $this->applicationSingleton,
            $this->logger,
            new RequestStack(),
            $this->feedbackStateHelper,
            $this->feedbackInfoCollector,
        );

        $this->feedbackInfoCollector->shouldNotReceive('collect');
        $this->feedbackStateHelper->shouldNotReceive('storeFeedbackInfo');

        $reporter->reportError(new Exception('test'), '');
    }

    #[Test]
    public function it_merges_exception_feedback_info_when_exception_implements_has_feedback_info_interface(): void
    {
        $exception = new class('test') extends Exception implements EngineBlock_Corto_Exception_HasFeedbackInfoInterface {
            public function getFeedbackInfo(): array
            {
                return ['customKey' => 'customValue', 'artCode' => 'overridden-art-code'];
            }
        };

        $this->feedbackInfoCollector
            ->shouldReceive('collect')
            ->andReturn(['artCode' => 'original-art-code', 'datetime' => '2026-01-01']);

        $this->feedbackStateHelper
            ->shouldReceive('storeFeedbackInfo')
            ->once()
            ->with([
                'artCode'   => 'overridden-art-code',
                'datetime'  => '2026-01-01',
                'customKey' => 'customValue',
            ]);

        $this->reporter->reportError($exception, '');
    }

    #[Test]
    public function it_stores_the_pep_policy_decision_in_the_session(): void
    {
        $decision = new stdClass();
        $exception = EngineBlock_Corto_Exception_PEPNoAccess::basedOn($decision);

        $this->reporter->reportError($exception, '');

        self::assertSame($decision, $this->session->get('error_authorization_policy_decision'));
    }
}
