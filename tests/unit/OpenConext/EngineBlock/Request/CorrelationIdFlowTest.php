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

/**
 * Integration test: simulates the complete 4-leg SAML authentication flow and
 * verifies that a single correlation ID flows through every leg via
 * CorrelationIdRepository.
 *
 *   Leg 1  SSO            SP AuthnRequest  ID = A  → mint → correlation_id = CX
 *   Leg 2  ContinueToIdp  ID = A (POST)            → resolve(A) → CX
 *                         EB AuthnRequest  ID = B  → link(B, A) → B also maps to CX
 *   Leg 3  ACS            IdP Response InResponseTo=B → resolve(B) → CX
 *   Leg 4  Consent        SP request ID = A        → resolve(A) → CX
 */
class CorrelationIdFlowTest extends TestCase
{
    private CorrelationId $correlationId;
    private CorrelationIdRepository $repo;
    private Session $session;
    private RequestStack $requestStack;

    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage());
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestStack->method('getSession')->willReturn($this->session);

        $this->correlationId = new CorrelationId();
        $this->repo = new CorrelationIdRepository($this->correlationId, $this->requestStack);
    }

    private function newRepo(CorrelationId $cid): CorrelationIdRepository
    {
        // All legs share the same session (same browser session across requests)
        return new CorrelationIdRepository($cid, $this->requestStack);
    }

    // ── WAYF path ────────────────────────────────────────────────────────────

    public function test_wayf_flow_all_four_legs_share_the_same_correlation_id(): void
    {
        $spRequestId  = '_sp-request-A';
        $idpRequestId = '_idp-request-B';

        // Leg 1 — SSO: mint the correlation ID.
        $this->repo->mint($spRequestId);
        $this->repo->resolve($spRequestId);
        $mintedCx = $this->correlationId->get();
        $this->assertNotNull($mintedCx, 'SSO must mint a correlation ID');

        // Leg 2 — ContinueToIdp: resolves SP request ID A.
        $freshCid = new CorrelationId();
        $this->newRepo($freshCid)->resolve($spRequestId);
        $this->assertSame($mintedCx, $freshCid->get(), 'ContinueToIdp must see the same correlation ID');

        // ProxyServer::sendAuthenticationRequest links the new IdP request ID to
        // the SP request ID (happens after ContinueToIdp, before IdP response).
        $this->repo->link($idpRequestId, $spRequestId);

        // Leg 3 — ACS: IdP response InResponseTo=B, resolves via B.
        $freshCid2 = new CorrelationId();
        $this->newRepo($freshCid2)->resolve($idpRequestId);
        $this->assertSame($mintedCx, $freshCid2->get(), 'ACS must see the same correlation ID');

        // Leg 4 — Consent: resolves SP request ID A again.
        $freshCid3 = new CorrelationId();
        $this->newRepo($freshCid3)->resolve($spRequestId);
        $this->assertSame($mintedCx, $freshCid3->get(), 'Consent must see the same correlation ID');
    }

    // ── Direct path (no WAYF) ─────────────────────────────────────────────────

    public function test_direct_flow_acs_and_consent_share_the_correlation_id_minted_at_sso(): void
    {
        $spRequestId  = '_sp-direct-A';
        $idpRequestId = '_idp-direct-B';

        $this->repo->mint($spRequestId);
        $this->repo->link($idpRequestId, $spRequestId);
        $this->repo->resolve($spRequestId);
        $mintedCx = $this->correlationId->get();
        $this->assertNotNull($mintedCx);

        $ids = $this->session->get('CorrelationIds');
        $this->assertSame($mintedCx, $ids[$idpRequestId], 'ACS resolves via IdP request ID');
        $this->assertSame($mintedCx, $ids[$spRequestId], 'Consent resolves via SP request ID');
    }

    // ── Concurrent flows ──────────────────────────────────────────────────────

    public function test_two_concurrent_flows_have_independent_correlation_ids(): void
    {
        $this->repo->mint('_sp-A1');
        $this->repo->link('_idp-B1', '_sp-A1');

        $this->repo->mint('_sp-A2');
        $this->repo->link('_idp-B2', '_sp-A2');

        $ids = $this->session->get('CorrelationIds');
        $cx1 = $ids['_sp-A1'];
        $cx2 = $ids['_sp-A2'];

        $this->assertNotNull($cx1);
        $this->assertNotNull($cx2);
        $this->assertNotSame($cx1, $cx2, 'Concurrent flows must have different correlation IDs');
        $this->assertSame($cx1, $ids['_idp-B1']);
        $this->assertSame($cx2, $ids['_idp-B2']);
    }

    // ── Back-button replay guard ───────────────────────────────────────────────

    public function test_replaying_an_sso_request_does_not_change_the_correlation_id(): void
    {
        $spRequestId = '_sp-replay-A';

        $this->repo->mint($spRequestId);
        $cx = $this->session->get('CorrelationIds')[$spRequestId];

        $this->repo->mint($spRequestId);

        $this->assertSame($cx, $this->session->get('CorrelationIds')[$spRequestId], 'Back-button replay must not change the correlation ID');
    }

    // ── Null safety ───────────────────────────────────────────────────────────

    public function test_unknown_request_id_does_not_set_correlation_id(): void
    {
        $this->repo->resolve('_unknown-id');
        $this->assertNull($this->correlationId->get(), 'Correlation ID must remain null for unknown request IDs');
    }
}
