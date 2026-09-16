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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use OpenConext\EngineBlockBundle\Exception\InvalidSbsResponseException;
use OpenConext\EngineBlockBundle\Sbs\Dto\AuthzRequest;
use OpenConext\EngineBlockBundle\Sbs\AuthzResponse;
use OpenConext\EngineBlockBundle\Sbs\SbsClientInterface;
use OpenConext\EngineBlockBundle\Sbs\SbsAttributeMerger;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use SAML2\Assertion;
use SAML2\AuthnRequest;
use SAML2\Response;

class EngineBlock_Test_Corto_Filter_Command_SramInterruptFilterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var ServiceProvider
     */
    private $sp;

    /**
     * @var MetadataRepositoryInterface
     */
    private $repository;

    public function setUp(): void
    {
        $this->sp = new ServiceProvider('SP');

        $this->repository = Mockery::mock(MetadataRepositoryInterface::class);
        $this->repository->shouldReceive('findServiceProviderByEntityId')
            ->andReturn($this->sp);
    }

    public function testItDoesNothingIfFeatureFlagNotEnabled(): void
    {
        $sbsClient = Mockery::mock(SbsClientInterface::class);

        $sramFilter = new EngineBlock_Corto_Filter_Command_SramInterruptFilter(
            $sbsClient,
            new FeatureConfiguration(['eb.feature_enable_sram_interrupt' => false]),
            new SbsAttributeMerger([]),
            new NullLogger(),
        );

        $request = $this->mockRequest();
        $sramFilter->setRequest($request);

        $sramFilter->execute();
        $this->assertEmpty($sramFilter->getResponseAttributes());
    }

    public function testItDoesNothingIfSpDoesNotHaveCollabEnabled(): void
    {
        $sbsClient = Mockery::mock(SbsClientInterface::class);

        $sramFilter = new EngineBlock_Corto_Filter_Command_SramInterruptFilter(
            $sbsClient,
            new FeatureConfiguration(['eb.feature_enable_sram_interrupt' => true]),
            new SbsAttributeMerger([]),
            new NullLogger(),
        );

        $server = Mockery::mock(EngineBlock_Corto_ProxyServer::class);
        $server->shouldReceive('getRepository')
            ->andReturn($this->repository);

        $sramFilter->setProxyServer($server);

        $request = $this->mockRequest();
        $sramFilter->setRequest($request);

        $sbsClient->shouldNotReceive('authz');

        $sp = $this->mockServiceProvider('spEntityId');
        $sp->expects('getCoins->collabEnabled')->andReturn(false);

        $sramFilter->setServiceProvider($sp);

        $sramFilter->execute();
        $this->assertEmpty($sramFilter->getResponseAttributes());
    }

    public function testItAddsNonceWhenMessageInterrupt(): void
    {
        $sbsClient = Mockery::mock(SbsClientInterface::class);

        $sramFilter = new EngineBlock_Corto_Filter_Command_SramInterruptFilter(
            $sbsClient,
            new FeatureConfiguration(['eb.feature_enable_sram_interrupt' => true]),
            new SbsAttributeMerger([]),
            new NullLogger(),
        );

        $initialAttributes = [
            'urn:mace:dir:attribute-def:uid' => ['userIdValue'],
            'urn:oasis:names:tc:SAML:attribute:subject-id' => ['subjectIdValue'],
            'urn:mace:dir:attribute-def:mail' => ['user@example.org'],
        ];
        $sramFilter->setResponseAttributes($initialAttributes);

        $server = Mockery::mock(EngineBlock_Corto_ProxyServer::class);
        $server->expects('getUrl')->andReturn('https://example.org');
        $server->shouldReceive('getRepository')
            ->andReturn($this->repository);

        $sramFilter->setProxyServer($server);

        $request = $this->mockRequest();
        $sramFilter->setRequest($request);

        $sramFilter->setResponse(new EngineBlock_Saml2_ResponseAnnotationDecorator(new Response()));

        $response = AuthzResponse::fromData([
            'msg' => 'interrupt',
            'nonce' => 'hash123',
            'attributes' => [
                'dummy' => 'attributes',
            ]
        ]);

        $expectedRequest = new AuthzRequest(
            userId: '',
            eduPersonPrincipalName: '',
            externalSubjectId: 'subjectIdValue',
            email: ['user@example.org'],
            continueUrl: 'https://example.org?ID=',
            serviceId: 'spEntityId',
            issuerId: 'idpEntityId',
            attributes: $initialAttributes,
        );

        $sbsClient->shouldReceive('authz')
            ->withArgs(function ($args) use ($expectedRequest) {
                return $args->userId === $expectedRequest->userId
                    && $args->eduPersonPrincipalName === $expectedRequest->eduPersonPrincipalName
                    && $args->externalSubjectId === $expectedRequest->externalSubjectId
                    && $args->email === $expectedRequest->email
                    && strpos($args->continueUrl, $expectedRequest->continueUrl) === 0
                    && $args->serviceId === $expectedRequest->serviceId
                    && $args->issuerId === $expectedRequest->issuerId
                    && $args->attributes === $expectedRequest->attributes;
            })
            ->andReturn($response);

        /** @var \Mockery\Mock|ServiceProvider $sp */
        $sp = $this->mockServiceProvider('spEntityId');
        $sp->expects('getCoins->collabEnabled')->andReturn(true);
        $sramFilter->setServiceProvider($sp);

        /** @var \Mockery\Mock|IdentityProvider $sp */
        $idp = $this->mockIdentityProvider('idpEntityId');
        $sramFilter->setIdentityProvider($idp);

        $sramFilter->execute();
        $this->assertSame($initialAttributes, $sramFilter->getResponseAttributes());
        $this->assertSame('hash123', $sramFilter->getResponse()->getSramInterruptNonce());
    }

    public function testItAddsSramAttributesOnStatusAuthorized(): void
    {
        $sbsClient = Mockery::mock(SbsClientInterface::class);

        $attributeMerger = new SbsAttributeMerger([
            'urn:mace:dir:attribute-def:uid',
            'urn:mace:dir:attribute-def:eduPersonEntitlement',
        ]);

        $sramFilter = new EngineBlock_Corto_Filter_Command_SramInterruptFilter(
            $sbsClient,
            new FeatureConfiguration(['eb.feature_enable_sram_interrupt' => true]),
            $attributeMerger,
            new NullLogger()
        );

        $initialAttributes = [
            'urn:mace:dir:attribute-def:uid' => ['userIdValue'],
            'urn:oasis:names:tc:SAML:attribute:subject-id' => ['subjectIdValue'],
            'urn:mace:dir:attribute-def:mail' => ['user@example.org'],
        ];
        $sramFilter->setResponseAttributes($initialAttributes);

        $server = Mockery::mock(EngineBlock_Corto_ProxyServer::class);
        $server->expects('getUrl')->andReturn('https://example.org');
        $server->shouldReceive('getRepository')
            ->andReturn($this->repository);

        $sramFilter->setProxyServer($server);

        $request = $this->mockRequest();
        $sramFilter->setRequest($request);

        $sramFilter->setResponse(new EngineBlock_Saml2_ResponseAnnotationDecorator(new Response()));


        $response = AuthzResponse::fromData([
            'msg' => 'authorized',
            'nonce' => 'hash123',
            'attributes' => [
                'urn:mace:dir:attribute-def:uid' => ['userIdValue'],
                'urn:mace:dir:attribute-def:eduPersonEntitlement' => 'attributes',
            ],
        ]);

        $expectedRequest = new AuthzRequest(
            userId: '',
            eduPersonPrincipalName: '',
            externalSubjectId: 'subjectIdValue',
            email: ['user@example.org'],
            continueUrl: 'https://example.org?ID=',
            serviceId: 'spEntityId',
            issuerId: 'idpEntityId',
            attributes: $initialAttributes,
        );

        $sbsClient->shouldReceive('authz')
            ->withArgs(function ($args) use ($expectedRequest) {

                return $args->userId === $expectedRequest->userId
                    && $args->eduPersonPrincipalName === $expectedRequest->eduPersonPrincipalName
                    && $args->externalSubjectId === $expectedRequest->externalSubjectId
                    && $args->email === $expectedRequest->email
                    && str_starts_with($args->continueUrl, $expectedRequest->continueUrl)
                    && $args->serviceId === $expectedRequest->serviceId
                    && $args->issuerId === $expectedRequest->issuerId
                    && $args->attributes === $expectedRequest->attributes;
            })
            ->andReturn($response);

        /** @var \Mockery\Mock|ServiceProvider $sp */
        $sp = $this->mockServiceProvider('spEntityId');
        $sp->expects('getCoins->collabEnabled')->andReturn(true);
        $sramFilter->setServiceProvider($sp);

        /** @var \Mockery\Mock|IdentityProvider $sp */
        $idp = $this->mockIdentityProvider('idpEntityId');
        $sramFilter->setIdentityProvider($idp);


        $expectedAttributes = [
            'urn:mace:dir:attribute-def:uid' => ['userIdValue'],
            'urn:oasis:names:tc:SAML:attribute:subject-id' => ['subjectIdValue'],
            'urn:mace:dir:attribute-def:mail' => ['user@example.org'],
            'urn:mace:dir:attribute-def:eduPersonEntitlement' => 'attributes',
        ];

        $sramFilter->execute();
        $this->assertSame($expectedAttributes, $sramFilter->getResponseAttributes());
        $this->assertSame('', $sramFilter->getResponse()->getSramInterruptNonce());
    }

    public function testThrowsEngineBlockExceptionIfPolicyCannotBeChecked()
    {
        $this->expectException(EngineBlock_Exception_SbsCheckFailed::class);
        $this->expectExceptionMessage('The SBS server could not be queried: Server could not be reached.');

        $sbsClient = Mockery::mock(SbsClientInterface::class);
        $sbsClient->expects('authz')->andThrows(new InvalidSbsResponseException('Server could not be reached.'));

        $sramFilter = new EngineBlock_Corto_Filter_Command_SramInterruptFilter(
            $sbsClient,
            new FeatureConfiguration(['eb.feature_enable_sram_interrupt' => true]),
            new SbsAttributeMerger([]),
            new NullLogger(),
        );

        $initialAttributes = ['urn:mace:dir:attribute-def:uid' => ['userIdValue']];
        $sramFilter->setResponseAttributes($initialAttributes);

        $server = Mockery::mock(EngineBlock_Corto_ProxyServer::class);
        $server->expects('getUrl')->andReturn('https://example.org');
        $server->shouldReceive('getRepository')
            ->andReturn($this->repository);

        $sramFilter->setProxyServer($server);

        $request = $this->mockRequest();
        $sramFilter->setRequest($request);

        $sramFilter->setResponse(new EngineBlock_Saml2_ResponseAnnotationDecorator(new Response()));


        /** @var \Mockery\Mock|ServiceProvider $sp */
        $sp = $this->mockServiceProvider('spEntityId');
        $sp->expects('getCoins->collabEnabled')->andReturn(true);
        $sramFilter->setServiceProvider($sp);

        /** @var \Mockery\Mock|IdentityProvider $sp */
        $idp = $this->mockIdentityProvider('idpEntityId');
        $sramFilter->setIdentityProvider($idp);

        $sramFilter->execute();
    }

    #[DataProvider('filterNonStringValuesProvider')]
    public function testFilterNonStringValuesFromAttributes(array $input, array $expected): void
    {
        $method = new ReflectionMethod(
            EngineBlock_Corto_Filter_Command_SramInterruptFilter::class,
            'filterNonStringValuesFromAttributes'
        );

        $this->assertSame($expected, $method->invoke(null, $input));
    }

    public static function filterNonStringValuesProvider(): array
    {
        return [
            // ---- happy paths: array of (possibly empty) arrays of strings ----
            'empty attribute set'       => [[], []],
            'single string value'       => [['a' => ['x']], ['a' => ['x']]],
            'multiple string values'    => [['a' => ['x', 'y']], ['a' => ['x', 'y']]],
            'empty inner array kept'    => [['a' => []], ['a' => []]],
            'several string attributes' => [
                ['a' => ['x'], 'b' => ['y', 'z'], 'c' => []],
                ['a' => ['x'], 'b' => ['y', 'z'], 'c' => []],
            ],

            // ---- failure paths: offending attribute dropped ----
            'inner has integer'         => [['a' => [1]], []],
            'inner has float'           => [['a' => [1.5]], []],
            'inner has bool'            => [['a' => [true]], []],
            'inner has null'            => [['a' => [null]], []],
            'inner has nested array'    => [['a' => [['nested']]], []],
            'inner has object'          => [['a' => [new stdClass()]], []],
            'inner mixed string + int'  => [['a' => ['x', 1]], []],
            'inner mixed string + obj'  => [['a' => ['x', new stdClass()]], []],

            // ---- mixed set: only offending attributes dropped, keys preserved ----
            'keeps good drops bad'      => [
                ['good' => ['x'], 'bad' => ['x', 2], 'empty' => []],
                ['good' => ['x'], 'empty' => []],
            ],
        ];
    }

    private function mockServiceProvider(string $entityId): ServiceProvider
    {
        $sp = Mockery::mock(ServiceProvider::class);
        $sp->entityId = $entityId;
        $sp->shouldReceive('getCoins->isTrustedProxy')->andReturn(false);
        $sp->shouldReceive('getCoins->policyEnforcementDecisionRequired')->andReturn(true);
        return $sp;
    }

    private function mockIdentityProvider(string $entityId): IdentityProvider
    {
        $idp = Mockery::mock(IdentityProvider::class);
        $idp->entityId = $entityId;
        return $idp;
    }

    private function mockRequest(): EngineBlock_Saml2_AuthnRequestAnnotationDecorator
    {
        $assertion = new Assertion();
        $request = new AuthnRequest();
        $response = new SAML2\Response();
        $response->setAssertions(array($assertion));
        return new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($request);
    }
}
