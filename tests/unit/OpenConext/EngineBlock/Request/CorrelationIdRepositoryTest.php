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
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CorrelationIdRepositoryTest extends TestCase
{
    private Session $session;
    private CorrelationIdRepository $repo;

    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage());

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')->willReturn($this->session);

        $this->repo = new CorrelationIdRepository($requestStack);
    }

    public function test_store_persists_correlation_id_in_session(): void
    {
        $cid = new CorrelationId('cx-abc123');

        $this->repo->store('_req-A', $cid);

        $ids = $this->session->get('CorrelationIds');
        $this->assertSame('cx-abc123', $ids['_req-A']);
    }

    public function test_find_returns_correlation_id_for_known_request(): void
    {
        $this->session->set('CorrelationIds', ['_req-A' => 'cx-abc123']);

        $result = $this->repo->find('_req-A');

        $this->assertInstanceOf(CorrelationId::class, $result);
        $this->assertSame('cx-abc123', $result->correlationId);
    }

    public function test_find_returns_null_for_unknown_request(): void
    {
        $result = $this->repo->find('_unknown');

        $this->assertNull($result);
    }

    public function test_link_copies_correlation_id_to_target_request(): void
    {
        $this->session->set('CorrelationIds', ['_sp-A' => 'cx-123']);

        $this->repo->link('_idp-B', '_sp-A');

        $this->assertSame('cx-123', $this->session->get('CorrelationIds')['_idp-B']);
    }

    public function test_link_with_unknown_source_is_a_noop(): void
    {
        $this->repo->link('_idp-B', '_unknown');

        $this->assertArrayNotHasKey('_idp-B', $this->session->get('CorrelationIds', []));
    }

    public function test_find_returns_stored_correlation_id(): void
    {
        $cid = new CorrelationId('cx-round-trip');
        $this->repo->store('_req-A', $cid);
        $result = $this->repo->find('_req-A');
        $this->assertSame('cx-round-trip', $result->correlationId);
    }

    public function test_store_is_noop_when_no_session_available(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')
            ->willThrowException(new SessionNotFoundException());

        $repo = new CorrelationIdRepository($requestStack);
        $repo->store('_req-A', new CorrelationId('cx-123'));

        // No exception thrown — that's the assertion
        $this->expectNotToPerformAssertions();
    }

    public function test_find_returns_null_when_no_session_available(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')
            ->willThrowException(new SessionNotFoundException());

        $repo = new CorrelationIdRepository($requestStack);

        $this->assertNull($repo->find('_req-A'));
    }

    public function test_link_is_noop_when_no_session_available(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')
            ->willThrowException(new SessionNotFoundException());

        $repo = new CorrelationIdRepository($requestStack);
        $repo->link('_idp-B', '_sp-A');

        $this->assertNull($this->session->get('CorrelationIds'));
    }
}
