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
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use OpenConext\EngineBlock\Http\HttpClient;
use OpenConext\EngineBlockBundle\Sbs\AttributesResponse;
use OpenConext\EngineBlockBundle\Sbs\AuthzResponse;
use OpenConext\EngineBlockBundle\Sbs\Dto\AttributesRequest;
use OpenConext\EngineBlockBundle\Sbs\Dto\AuthzRequest;
use OpenConext\EngineBlockBundle\Sbs\SbsClient;
use PHPUnit\Framework\TestCase;

class SbsClientTest extends TestCase
{
    private $httpClient;
    private $sbsClient;
    private $guzzleMock;
    private TestHandler $logHandler;

    protected function setUp(): void
    {
        $this->guzzleMock = $this->createMock(ClientInterface::class);
        $this->httpClient = $this->createMock(HttpClient::class);
        $this->logHandler = new TestHandler();

        $this->sbsClient = new SbsClient(
            $this->httpClient,
            'https://sbs.example.com/',
            '/authz',
            '/authz',
            '/interrupt',
            'Bearer test_token',
            true,
            new Logger('test', [$this->logHandler])
        );
    }

    public function testAuthz(): void
    {
        $requestMock = $this->authzRequest();
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
        $requestMock = new AttributesRequest('nonce');
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

    public function testAuthzLogsRequestAndResponse(): void
    {
        $this->httpClient->method('post')->willReturn(['msg' => 'interrupt', 'nonce' => 'secret-nonce']);

        $this->sbsClient->authz($this->authzRequest());

        $this->assertTrue($this->logHandler->hasInfoThatContains('Sending SBS authz request for service "https://sp.example.com" and issuer "https://idp.example.com"'));
        $this->assertTrue($this->logHandler->hasDebugThatContains('SBS authz request attributes sent: uid, mail'));
        $this->assertTrue($this->logHandler->hasDebugThatPasses(
            fn($record) => $record->message === 'SBS authz response received'
                && $record->context === ['response' => ['msg' => 'interrupt', 'nonce' => 'secret-nonce']]
        ));
        foreach ($this->logHandler->getRecords() as $record) {
            $this->assertStringNotContainsString('******', $record->message);
        }
    }

    public function testRequestAttributesForLogsRequestAndResponse(): void
    {
        $this->httpClient->method('post')->willReturn(['msg' => 'authorized', 'attributes' => ['name' => 'value']]);

        $this->sbsClient->requestAttributesFor(new AttributesRequest('nonce'));

        $this->assertTrue($this->logHandler->hasInfoThatContains('Sending SBS attributes request'));
        $this->assertTrue($this->logHandler->hasInfoThatContains('Received SBS attributes response'));
        $this->assertTrue($this->logHandler->hasDebugThatPasses(
            fn($record) => $record->message === 'SBS attributes response received'
                && $record->context === ['response' => ['msg' => 'authorized', 'attributes' => ['name' => 'value']]]
        ));
    }

    private function authzRequest(): AuthzRequest
    {
        return new AuthzRequest(
            userId: 'user',
            eduPersonPrincipalName: 'eppn',
            externalSubjectId: 'sub',
            email: ['user@example.com'],
            continueUrl: 'https://engine.example.com/continue',
            serviceId: 'https://sp.example.com',
            issuerId: 'https://idp.example.com',
            attributes: ['uid' => ['user'], 'mail' => ['user@example.com']]
        );
    }
}
