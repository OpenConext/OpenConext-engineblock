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
use Psr\Log\NullLogger;
use SAML2\AuthnRequest;
use SAML2\XML\saml\Issuer;

class EngineBlock_Test_Saml2_AuthnRequestSessionRepositoryTest extends TestCase
{
    private EngineBlock_Saml2_AuthnRequestSessionRepository $repo;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->repo = new EngineBlock_Saml2_AuthnRequestSessionRepository(new NullLogger());
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
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

        $_SESSION['SAMLRequest'] = ['_r' => $this->makeRequest('_r', 'https://sp-b')];

        $this->assertSame($spRequest, $this->repo->findLinkedRequest('_eb1'));
    }

    public function test_find_linked_request_returns_null_for_legacy_link_without_request(): void
    {
        $_SESSION['SAMLRequest'] = ['_r' => $this->makeRequest('_r', 'https://sp-a')];
        $_SESSION['SAMLRequestLinks'] = ['_eb1' => '_r'];
        $repo = new EngineBlock_Saml2_AuthnRequestSessionRepository(new NullLogger());

        $this->assertNull($repo->findLinkedRequest('_eb1'));
        $this->assertNull($repo->findLinkedRequest(null));
    }
}
