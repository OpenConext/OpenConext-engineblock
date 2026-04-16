<?php

/**
 * Copyright 2010 SURFnet B.V.
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

class CorrelationIdRepositoryTest extends TestCase
{
    private CorrelationId $correlationId;
    private Session $session;
    private CorrelationIdRepository $repo;

    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage());
        $this->correlationId = new CorrelationId();

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')->willReturn($this->session);

        $this->repo = new CorrelationIdRepository($this->correlationId, $requestStack);
    }

    public function test_mint_generates_a_correlation_id_for_a_new_request_id(): void
    {
        $this->repo->mint('_req-A');

        $ids = $this->session->get('CorrelationIds');
        $this->assertArrayHasKey('_req-A', $ids);
        $this->assertNotEmpty($ids['_req-A']);
    }

    public function test_mint_does_not_overwrite_an_existing_correlation_id(): void
    {
        $this->session->set('CorrelationIds', ['_req-A' => 'cx-existing']);

        $this->repo->mint('_req-A');

        $this->assertSame('cx-existing', $this->session->get('CorrelationIds')['_req-A']);
    }

    public function test_link_copies_the_correlation_id_to_the_new_request_id(): void
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

    public function test_resolve_pushes_correlation_id_into_the_service(): void
    {
        $this->session->set('CorrelationIds', ['_req-A' => 'cx-expected']);

        $this->repo->resolve('_req-A');

        $this->assertSame('cx-expected', $this->correlationId->get());
    }

    public function test_resolve_with_unknown_id_does_not_overwrite_existing_value(): void
    {
        $this->correlationId->set('cx-existing');

        $this->repo->resolve('_unknown');

        $this->assertSame('cx-existing', $this->correlationId->get());
    }

    public function test_resolve_with_null_does_not_overwrite_existing_value(): void
    {
        $this->correlationId->set('cx-existing');

        $this->repo->resolve(null);

        $this->assertSame('cx-existing', $this->correlationId->get());
    }

    public function test_mint_then_link_then_resolve_via_linked_id_returns_same_value(): void
    {
        $this->repo->mint('_sp-A');
        $mintedCx = $this->session->get('CorrelationIds')['_sp-A'];

        $this->repo->link('_idp-B', '_sp-A');
        $this->repo->resolve('_idp-B');

        $this->assertSame($mintedCx, $this->correlationId->get());
    }

    // ── SessionNotFoundException safety ───────────────────────────────────────

    public function test_mint_is_noop_when_no_session_available(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')
            ->willThrowException(new \Symfony\Component\HttpFoundation\Exception\SessionNotFoundException());

        $repo = new CorrelationIdRepository($this->correlationId, $requestStack);
        $repo->mint('_req-A');

        $this->assertNull($this->correlationId->get());
    }

    public function test_link_is_noop_when_no_session_available(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')
            ->willThrowException(new \Symfony\Component\HttpFoundation\Exception\SessionNotFoundException());

        $repo = new CorrelationIdRepository($this->correlationId, $requestStack);
        $repo->link('_idp-B', '_sp-A');

        $this->assertNull($this->session->get('CorrelationIds'));
    }

    public function test_resolve_is_noop_when_no_session_available(): void
    {
        $this->correlationId->set('cx-existing');

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')
            ->willThrowException(new \Symfony\Component\HttpFoundation\Exception\SessionNotFoundException());

        $repo = new CorrelationIdRepository($this->correlationId, $requestStack);
        $repo->resolve('_req-A');

        $this->assertSame('cx-existing', $this->correlationId->get());
    }
}
