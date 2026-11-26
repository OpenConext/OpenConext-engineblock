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

use PHPUnit\Framework\TestCase;
use SAML2\AuthnRequest;
use SAML2\XML\saml\Issuer;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class EngineBlock_Test_Saml2_AuthnRequestSessionRepositoryTest extends TestCase
{
    private Session $session;
    private EngineBlock_Saml2_AuthnRequestSessionRepository $repo;

    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage());
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')->willReturn($this->session);
        $this->repo = new EngineBlock_Saml2_AuthnRequestSessionRepository($requestStack);
    }

    private function makeRequest(
        string $id,
        ?string $issuer = null,
        array $requesterIds = []
    ): EngineBlock_Saml2_AuthnRequestAnnotationDecorator {
        $authnRequest = new AuthnRequest();
        $authnRequest->setId($id);
        $authnRequest->setRequesterID($requesterIds);
        if ($issuer !== null) {
            $issuerElement = new Issuer();
            $issuerElement->setValue($issuer);
            $authnRequest->setIssuer($issuerElement);
        }
        return new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($authnRequest);
    }

    public function test_store_saves_request(): void
    {
        $request = $this->makeRequest('_sp-request-A');

        $this->repo->store($request);

        $this->assertSame($request, $this->repo->findRequestById('_sp-request-A'));
    }

    public function test_request_id_reused_by_other_issuer_is_rejected(): void
    {
        $first = $this->makeRequest('_r', 'https://sp-a');
        $second = $this->makeRequest('_r', 'https://sp-b');
        $this->repo->store($first);

        try {
            $this->repo->assertRequestIdNotUsedByOtherIssuer($second);
            $this->fail('Expected reuse of the request ID by another issuer to be rejected');
        } catch (EngineBlock_Corto_Module_Bindings_Exception $e) {
        }

        $this->expectException(EngineBlock_Corto_Module_Bindings_Exception::class);
        try {
            $this->repo->store($second);
        } finally {
            $this->assertSame($first, $this->repo->findRequestById('_r'));
        }
    }

    public function test_request_id_reused_by_same_issuer_is_accepted(): void
    {
        $first = $this->makeRequest('_r', 'https://sp-a');
        $resubmit = $this->makeRequest('_r', 'https://sp-a');

        $this->repo->store($first);
        $this->repo->assertRequestIdNotUsedByOtherIssuer($resubmit);
        $this->repo->store($resubmit);

        $this->assertSame($resubmit, $this->repo->findRequestById('_r'));
    }

    public function test_request_id_reused_for_other_sp_behind_same_proxy_is_rejected(): void
    {
        $first = $this->makeRequest('_r', 'https://proxy', ['https://sp-a']);
        $this->repo->store($first);

        $this->expectException(EngineBlock_Corto_Module_Bindings_Exception::class);
        try {
            $this->repo->assertRequestIdNotUsedByOtherIssuer(
                $this->makeRequest('_r', 'https://proxy', ['https://sp-a', 'https://sp-b'])
            );
        } finally {
            $this->assertSame($first, $this->repo->findRequestById('_r'));
        }
    }

    public function test_request_id_reused_by_proxy_without_requester_id_is_rejected(): void
    {
        $this->repo->store($this->makeRequest('_r', 'https://proxy', ['https://sp-a']));

        $this->expectException(EngineBlock_Corto_Module_Bindings_Exception::class);
        $this->repo->assertRequestIdNotUsedByOtherIssuer($this->makeRequest('_r', 'https://proxy'));
    }

    public function test_request_id_reused_for_same_sp_behind_same_proxy_is_accepted(): void
    {
        $this->repo->store($this->makeRequest('_r', 'https://proxy', ['https://sp-x', 'https://sp-a']));
        $resubmit = $this->makeRequest('_r', 'https://proxy', ['https://sp-a']);

        $this->repo->assertRequestIdNotUsedByOtherIssuer($resubmit);
        $this->repo->store($resubmit);

        $this->assertSame($resubmit, $this->repo->findRequestById('_r'));
    }

    public function test_link_stores_the_linked_request(): void
    {
        $spRequest = $this->makeRequest('_r', 'https://sp-a');
        $this->repo->store($spRequest);
        $this->repo->link($this->makeRequest('_eb1'), $spRequest);

        $this->assertSame($spRequest, $this->repo->findLinkedRequest('_eb1'));
    }

    public function test_linked_request_is_not_affected_by_overwriting_the_stored_request(): void
    {
        $spRequest = $this->makeRequest('_r', 'https://sp-a');
        $this->repo->store($spRequest);
        $this->repo->link($this->makeRequest('_eb1'), $spRequest);

        $this->session->set('SAMLRequest', ['_r' => $this->makeRequest('_r', 'https://sp-b')]);

        $this->assertSame($spRequest, $this->repo->findLinkedRequest('_eb1'));
    }

    public function test_find_linked_request_returns_null_for_legacy_link_without_request(): void
    {
        $this->session->set('SAMLRequest', ['_r' => $this->makeRequest('_r', 'https://sp-a')]);
        $this->session->set('SAMLRequestLinks', ['_eb1' => '_r']);

        $this->assertNull($this->repo->findLinkedRequest('_eb1'));
        $this->assertNull($this->repo->findLinkedRequest(null));
    }

    public function test_find_request_by_id_returns_null_for_unknown_id(): void
    {
        $this->assertNull($this->repo->findRequestById('_unknown'));
    }

    public function test_link_stores_request_mapping(): void
    {
        $spRequest  = $this->makeRequest('_sp-request-A');
        $idpRequest = $this->makeRequest('_idp-request-B');

        $this->repo->store($spRequest);
        $this->repo->link($idpRequest, $spRequest);

        $this->assertSame('_sp-request-A', $this->repo->findLinkedRequestId('_idp-request-B'));
    }

    public function test_find_linked_request_id_returns_null_for_unknown_id(): void
    {
        $this->assertNull($this->repo->findLinkedRequestId('_unknown'));
    }

    public function test_find_linked_request_id_returns_null_for_null_input(): void
    {
        $this->assertNull($this->repo->findLinkedRequestId(null));
    }

    public function test_store_and_find_multiple_requests(): void
    {
        $req1 = $this->makeRequest('_req-1');
        $req2 = $this->makeRequest('_req-2');

        $this->repo->store($req1);
        $this->repo->store($req2);

        $this->assertSame($req1, $this->repo->findRequestById('_req-1'));
        $this->assertSame($req2, $this->repo->findRequestById('_req-2'));
    }

    public function test_store_is_noop_when_no_session_available(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')
            ->willThrowException(new SessionNotFoundException());

        $repo = new EngineBlock_Saml2_AuthnRequestSessionRepository($requestStack);
        $request = $this->makeRequest('_req-A');

        $repo->store($request); // must not throw

        $this->assertNull($this->repo->findRequestById('_req-A'));
    }

    public function test_link_is_noop_when_no_session_available(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')
            ->willThrowException(new SessionNotFoundException());

        $repo = new EngineBlock_Saml2_AuthnRequestSessionRepository($requestStack);
        $spRequest  = $this->makeRequest('_sp-A');
        $idpRequest = $this->makeRequest('_idp-B');

        $repo->link($idpRequest, $spRequest); // must not throw

        $this->assertNull($this->repo->findLinkedRequestId('_idp-B'));
    }

    public function test_find_request_is_noop_when_no_session_available(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')
            ->willThrowException(new SessionNotFoundException());

        $repo = new EngineBlock_Saml2_AuthnRequestSessionRepository($requestStack);

        $this->assertNull($repo->findRequestById('_req-A'));
    }

    public function test_find_linked_is_noop_when_no_session_available(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getSession')
            ->willThrowException(new SessionNotFoundException());

        $repo = new EngineBlock_Saml2_AuthnRequestSessionRepository($requestStack);

        $this->assertNull($repo->findLinkedRequestId('_req-A'));
    }
}
