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

use Exception;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\EntityNotFoundException;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlock\Request\RequestId;
use OpenConext\EngineBlock\Request\RequestIdGenerator;
use OpenConext\EngineBlockBundle\Localization\LanguageSupportProvider;
use OpenConext\EngineBlockBundle\Localization\LocaleProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class FeedbackInfoCollectorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private FeedbackInfoCollector $collector;
    private MetadataRepositoryInterface $metadataRepository;
    private FeedbackStateHelperInterface $feedbackStateHelper;

    protected function setUp(): void
    {
        $request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $requestId = new RequestId(new class implements RequestIdGenerator {
            public function generateRequestId(): string
            {
                return 'test-request-id';
            }
        });

        $this->metadataRepository = m::mock(MetadataRepositoryInterface::class);
        $this->metadataRepository->shouldReceive('fetchServiceProviderByEntityId')
            ->andThrow(new EntityNotFoundException(''))->byDefault();
        $this->metadataRepository->shouldReceive('fetchIdentityProviderByEntityId')
            ->andThrow(new EntityNotFoundException(''))->byDefault();

        $localeProvider = new LocaleProvider(
            new LanguageSupportProvider(['en', 'nl'], ['en', 'nl']),
            'en',
        );

        $this->feedbackStateHelper = m::mock(FeedbackStateHelperInterface::class);
        $this->feedbackStateHelper->shouldReceive('getActiveFlowContext')->andReturn([])->byDefault();

        $this->collector = new FeedbackInfoCollector(
            $requestStack,
            $requestId,
            $this->metadataRepository,
            $localeProvider,
            $this->feedbackStateHelper,
        );
    }

    #[Test]
    public function collect_includes_standard_request_fields(): void
    {
        $this->metadataRepository->shouldIgnoreMissing();

        $info = $this->collector->collect(new Exception('oops'));

        self::assertArrayHasKey('datetime', $info);
        self::assertArrayHasKey('requestUrl', $info);
        self::assertArrayHasKey('requestId', $info);
        self::assertArrayHasKey('ipAddress', $info);
        self::assertArrayHasKey('artCode', $info);
        self::assertSame('test-request-id', $info['requestId']);
    }

    #[Test]
    public function collect_includes_sp_and_idp_names_from_session(): void
    {
        $this->feedbackStateHelper->shouldReceive('getActiveFlowContext')->andReturn([
            'serviceProvider'  => 'https://sp.example.com',
            'identityProvider' => 'https://idp.example.com',
        ]);

        $sp = m::mock(ServiceProvider::class);
        $sp->shouldReceive('getDisplayName')->andReturn('My SP');

        $idp = m::mock(IdentityProvider::class);
        $idp->shouldReceive('getDisplayName')->andReturn('My IdP');

        $this->metadataRepository
            ->shouldReceive('fetchServiceProviderByEntityId')
            ->with('https://sp.example.com')
            ->andReturn($sp);

        $this->metadataRepository
            ->shouldReceive('fetchIdentityProviderByEntityId')
            ->with('https://idp.example.com')
            ->andReturn($idp);

        $info = $this->collector->collect(new Exception('oops'));

        self::assertSame('https://sp.example.com', $info['serviceProvider']);
        self::assertSame('My SP', $info['serviceProviderName']);
        self::assertSame('https://idp.example.com', $info['identityProvider']);
        self::assertSame('My IdP', $info['identityProviderName']);
    }

    #[Test]
    public function collect_uses_original_sp_when_proxy_context_is_set(): void
    {
        $this->feedbackStateHelper->shouldReceive('getActiveFlowContext')->andReturn([
            'serviceProvider'         => 'https://proxy.example.com',
            'originalServiceProvider' => 'https://realsp.example.com',
            'proxyServiceProvider'    => 'https://proxy.example.com',
        ]);

        $sp = m::mock(ServiceProvider::class);
        $sp->shouldReceive('getDisplayName')->andReturn('Real SP');

        $this->metadataRepository
            ->shouldReceive('fetchServiceProviderByEntityId')
            ->with('https://realsp.example.com')
            ->andReturn($sp);

        $info = $this->collector->collect(new Exception('oops'));

        self::assertSame('https://realsp.example.com', $info['serviceProvider']);
        self::assertSame('Real SP', $info['serviceProviderName']);
        self::assertSame('https://proxy.example.com', $info['proxyServiceProvider']);
    }

    #[Test]
    public function collect_returns_empty_name_when_entity_not_found(): void
    {
        $this->feedbackStateHelper->shouldReceive('getActiveFlowContext')->andReturn([
            'serviceProvider' => 'https://unknown.example.com',
        ]);

        $this->metadataRepository
            ->shouldReceive('fetchServiceProviderByEntityId')
            ->andThrow(new EntityNotFoundException('not found'));

        $info = $this->collector->collect(new Exception('oops'));

        self::assertSame('https://unknown.example.com', $info['serviceProvider']);
        self::assertSame('', $info['serviceProviderName']);
    }
}
