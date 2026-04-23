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

namespace OpenConext\EngineBlock\Request;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CorrelationIdServiceTest extends TestCase
{
    private Session $session;
    private CorrelationIdRepository $repository;
    private CurrentCorrelationId $current;
    private CorrelationIdService $service;

    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage());

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')->willReturn($this->session);

        $this->repository = new CorrelationIdRepository($requestStack);
        $this->current = new CurrentCorrelationId();
        $this->service = new CorrelationIdService($this->repository, $this->current);
    }

    public function test_mint_stores_a_new_correlation_id_when_none_exists(): void
    {
        $this->service->mint('_req-A');

        $ids = $this->session->get('CorrelationIds');
        $this->assertArrayHasKey('_req-A', $ids);
        $this->assertNotEmpty($ids['_req-A']);
    }

    public function test_mint_does_not_overwrite_when_one_already_exists(): void
    {
        $this->session->set('CorrelationIds', ['_req-A' => 'cx-existing']);

        $this->service->mint('_req-A');

        $this->assertSame('cx-existing', $this->session->get('CorrelationIds')['_req-A']);
    }

    public function test_link_copies_correlation_id_to_target(): void
    {
        $this->session->set('CorrelationIds', ['_sp-A' => 'cx-123']);

        $this->service->link('_idp-B', '_sp-A');

        $this->assertSame('cx-123', $this->session->get('CorrelationIds')['_idp-B']);
    }

    public function test_resolve_sets_current_correlation_id_when_found(): void
    {
        $this->session->set('CorrelationIds', ['_req-A' => 'cx-abc123']);

        $this->service->resolve('_req-A');

        $this->assertSame('cx-abc123', $this->current->correlationId);
    }

    public function test_resolve_does_not_change_current_when_not_found(): void
    {
        $this->service->resolve('_unknown');

        $this->assertNull($this->current->correlationId);
    }

    public function test_resolve_with_null_is_a_noop(): void
    {
        $this->service->resolve(null);

        $this->assertNull($this->current->correlationId);
    }

    public function test_mint_then_resolve_sets_current_correlation_id(): void
    {
        $this->service->mint('_req-A');
        $this->service->resolve('_req-A');

        $this->assertNotNull($this->current->correlationId);
        $this->assertNotEmpty($this->current->correlationId);
    }
}
