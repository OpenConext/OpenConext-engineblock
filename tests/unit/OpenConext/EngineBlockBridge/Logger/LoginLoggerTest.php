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

namespace OpenConext\EngineBlockBridge\Logger;

use EngineBlock_Saml2_AuthnRequestAnnotationDecorator;
use EngineBlock_Saml2_ResponseAnnotationDecorator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use PHPUnit\Framework\TestCase;
use SAML2\Assertion;
use SAML2\AuthnRequest;
use SAML2\Response as SAMLResponse;
use SAML2\XML\saml\NameID;

class LoginLoggerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testLogLoginCallsAuthenticationLoggerWithCorrectParameters(): void
    {
        $authLogger = Mockery::mock(AuthenticationLoggerAdapter::class);
        $authLogger->shouldReceive('logLogin')->once()->withArgs(function (
            ServiceProvider $sp,
            IdentityProvider $idp,
            string $collabPersonId,
            ?string $keyId,
            array $requesterChain,
            string $nameId,
            ?string $authnContext,
            ?string $destination,
            ?array $idpList,
            array $logAttributes
        ) {
            return $collabPersonId === 'urn:collab:person:example.com:admin'
                && $nameId === 'some-name-id'
                && $logAttributes === ['email' => 'admin@example.com'];
        });

        $configuredLogAttributes = ['email' => 'urn:mace:dir:attribute-def:mail'];

        $logger = new LoginLogger($authLogger, $configuredLogAttributes);

        $response = $this->createResponse('some-name-id');
        $request = $this->createRequest();
        $sp = new ServiceProvider('https://sp.example.com');
        $idp = new IdentityProvider('https://idp.example.com');
        $repository = Mockery::mock(MetadataRepositoryInterface::class);
        $repository->shouldReceive('fetchServiceProviderByEntityId')->andReturn($sp);

        $responseAttributes = [
            'urn:mace:dir:attribute-def:mail' => ['admin@example.com'],
        ];

        $logger->logLogin(
            $response,
            $request,
            $sp,
            $idp,
            $repository,
            'urn:collab:person:example.com:admin',
            $responseAttributes,
        );
    }

    public function testLogLoginFiltersOnlyConfiguredAttributes(): void
    {
        $authLogger = Mockery::mock(AuthenticationLoggerAdapter::class);
        $authLogger->shouldReceive('logLogin')->once()->withArgs(function (
            ServiceProvider $sp,
            IdentityProvider $idp,
            string $collabPersonId,
            ?string $keyId,
            array $requesterChain,
            string $nameId,
            ?string $authnContext,
            ?string $destination,
            ?array $idpList,
            array $logAttributes
        ) {
            // Only 'uid' should be in logAttributes (mail is not in responseAttributes)
            return $logAttributes === ['uid' => 'admin'];
        });

        $configuredLogAttributes = [
            'uid' => 'urn:mace:dir:attribute-def:uid',
            'email' => 'urn:mace:dir:attribute-def:mail',
        ];

        $logger = new LoginLogger($authLogger, $configuredLogAttributes);

        $response = $this->createResponse('name-id');
        $request = $this->createRequest();
        $sp = new ServiceProvider('https://sp.example.com');
        $idp = new IdentityProvider('https://idp.example.com');
        $repository = Mockery::mock(MetadataRepositoryInterface::class);
        $repository->shouldReceive('fetchServiceProviderByEntityId')->andReturn($sp);

        $responseAttributes = [
            'urn:mace:dir:attribute-def:uid' => ['admin'],
        ];

        $logger->logLogin(
            $response,
            $request,
            $sp,
            $idp,
            $repository,
            'urn:collab:person:example.com:admin',
            $responseAttributes,
        );
    }

    private function createResponse(string $nameId = ''): EngineBlock_Saml2_ResponseAnnotationDecorator
    {
        $assertion = new Assertion();
        $assertion->setAuthnContextClassRef('urn:oasis:names:tc:SAML:2.0:ac:classes:Password');
        if ($nameId !== '') {
            $nameIdObj = new NameID();
            $nameIdObj->setValue($nameId);
            $assertion->setNameId($nameIdObj);
        }

        $samlResponse = new SAMLResponse();
        $samlResponse->setAssertions([$assertion]);

        return new EngineBlock_Saml2_ResponseAnnotationDecorator($samlResponse);
    }

    private function createRequest(): EngineBlock_Saml2_AuthnRequestAnnotationDecorator
    {
        return new EngineBlock_Saml2_AuthnRequestAnnotationDecorator(new AuthnRequest());
    }
}
