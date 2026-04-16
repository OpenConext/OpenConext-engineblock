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

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use SAML2\AuthnRequest;

class EngineBlock_Test_Saml2_AuthnRequestSessionRepositoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    private function makeRequest(string $id): EngineBlock_Saml2_AuthnRequestAnnotationDecorator
    {
        $authnRequest = new AuthnRequest();
        $authnRequest->setId($id);
        return new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($authnRequest);
    }

    private function makeRepository(): EngineBlock_Saml2_AuthnRequestSessionRepository
    {
        $logger = m::mock(Psr\Log\LoggerInterface::class);
        return new EngineBlock_Saml2_AuthnRequestSessionRepository($logger);
    }

    public function test_store_saves_request()
    {
        $repository = $this->makeRepository();
        $request = $this->makeRequest('_sp-request-A');

        $repository->store($request);

        $storedRequest = $repository->findRequestById('_sp-request-A');
        $this->assertSame($request, $storedRequest);
    }

    public function test_link_stores_request_mapping()
    {
        $repository = $this->makeRepository();
        $spRequest = $this->makeRequest('_sp-request-A');
        $idpRequest = $this->makeRequest('_idp-request-B');

        $repository->store($spRequest);
        $repository->link($idpRequest, $spRequest);

        $linkedRequestId = $repository->findLinkedRequestId('_idp-request-B');
        $this->assertSame('_sp-request-A', $linkedRequestId);
    }
}
