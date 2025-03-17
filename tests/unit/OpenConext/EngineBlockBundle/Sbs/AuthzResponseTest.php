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
use OpenConext\EngineBlockBundle\Exception\InvalidSbsResponseException;
use OpenConext\EngineBlockBundle\Sbs\AuthzResponse;
use OpenConext\EngineBlockBundle\Sbs\Dto\AuthzRequest;
use OpenConext\EngineBlockBundle\Sbs\Dto\EntitlementsRequest;
use OpenConext\EngineBlockBundle\Sbs\EntitlementsResponse;
use OpenConext\EngineBlockBundle\Sbs\SbsClient;
use OpenConext\EngineBlockBundle\Sbs\SbsClientInterface;
use PHPUnit\Framework\TestCase;

class AuthzResponseTest extends TestCase
{
    public function testFromDataValidAuthorizedResponse(): void
    {
        $jsonData = [
            'msg' => SbsClientInterface::AUTHORIZED,
            'attributes' => ['role' => 'admin']
        ];

        $response = AuthzResponse::fromData($jsonData);

        $this->assertInstanceOf(AuthzResponse::class, $response);
        $this->assertEquals(SbsClientInterface::AUTHORIZED, $response->msg);
        $this->assertEquals(['role' => 'admin'], $response->attributes);
        $this->assertNull($response->nonce);
    }

    public function testFromDataValidInterruptResponse(): void
    {
        $jsonData = [
            'msg' => SbsClientInterface::INTERRUPT,
            'nonce' => 'random_nonce'
        ];

        $response = AuthzResponse::fromData($jsonData);

        $this->assertInstanceOf(AuthzResponse::class, $response);
        $this->assertEquals(SbsClientInterface::INTERRUPT, $response->msg);
        $this->assertEquals('random_nonce', $response->nonce);
        $this->assertEmpty($response->attributes);
    }

    public function testFromDataMissingMsgThrowsException(): void
    {
        $this->expectException(InvalidSbsResponseException::class);
        $this->expectExceptionMessage('Key: "msg" was not found in the SBS response');

        AuthzResponse::fromData([]);
    }

    public function testFromDataInvalidMsgThrowsException(): void
    {
        $this->expectException(InvalidSbsResponseException::class);
        $this->expectExceptionMessage('Msg: "INVALID" is not a valid message');

        AuthzResponse::fromData(['msg' => 'INVALID']);
    }

    public function testFromDataInterruptWithoutNonceThrowsException(): void
    {
        $this->expectException(InvalidSbsResponseException::class);
        $this->expectExceptionMessage('Key: "nonce" was not found in the SBS response');

        AuthzResponse::fromData(['msg' => SbsClientInterface::INTERRUPT]);
    }

    public function testFromDataAuthorizedWithoutAttributesThrowsException(): void
    {
        $this->expectException(InvalidSbsResponseException::class);
        $this->expectExceptionMessage('Key: "attributes" was not found in the SBS response');

        AuthzResponse::fromData(['msg' => SbsClientInterface::AUTHORIZED]);
    }

    public function testFromDataAttributesNotArrayDefaultsToEmpty(): void
    {
        $jsonData = [
            'msg' => SbsClientInterface::AUTHORIZED,
            'attributes' => 'invalid_type'
        ];

        $response = AuthzResponse::fromData($jsonData);

        $this->assertInstanceOf(AuthzResponse::class, $response);
        $this->assertEquals(SbsClientInterface::AUTHORIZED, $response->msg);
        $this->assertEmpty($response->attributes);
    }
}
