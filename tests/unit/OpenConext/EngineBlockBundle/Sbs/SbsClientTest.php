<?php

/**
 * Copyright 2025 SURFnet B.V.
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

use GuzzleHttp\ClientInterface;
use OpenConext\EngineBlock\Http\HttpClient;
use OpenConext\EngineBlockBundle\Sbs\AuthzResponse;
use OpenConext\EngineBlockBundle\Sbs\Dto\AuthzRequest;
use OpenConext\EngineBlockBundle\Sbs\Dto\AttributesRequest;
use OpenConext\EngineBlockBundle\Sbs\AttributesResponse;
use OpenConext\EngineBlockBundle\Sbs\SbsClient;
use PHPUnit\Framework\TestCase;

class SbsClientTest extends TestCase
{
    private $httpClient;
    private $sbsClient;
    private $guzzleMock;

    protected function setUp(): void
    {
        $this->guzzleMock = $this->createMock(ClientInterface::class);
        $this->httpClient = $this->createMock(HttpClient::class);

        $this->sbsClient = new SbsClient(
            $this->httpClient,
            'https://sbs.example.com/',
            '/authz',
            '/authz',
            '/interrupt',
            'Bearer test_token',
            true
        );
    }

    public function testAuthz(): void
    {
        $requestMock = $this->createMock(AuthzRequest::class);
        $jsonResponse = ['msg' => 'interrupt', 'nonce' => 'hash'];

        $this->httpClient->expects($this->once())
            ->method('post')
            ->with(
                $this->anything(),
                'https://sbs.example.com/authz',
                [],
                [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer test_token',
                ]
            )
            ->willReturn($jsonResponse);

        $authzResponse = $this->sbsClient->authz($requestMock);

        $this->assertInstanceOf(AuthzResponse::class, $authzResponse);
    }

    public function testRequestAttributesFor(): void
    {
        $requestMock = $this->createMock(AttributesRequest::class);
        $jsonResponse = [
            'msg' => 'authorized',
            'attributes' => ['name' => 'value']
        ];

        $this->httpClient->expects($this->once())
            ->method('post')
            ->with(
                $this->anything(),
                'https://sbs.example.com/authz',
                [],
                [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer test_token',
                ]
            )
            ->willReturn($jsonResponse);

        $attributesResponse = $this->sbsClient->requestAttributesFor($requestMock);

        $this->assertInstanceOf(AttributesResponse::class, $attributesResponse);
    }
}
