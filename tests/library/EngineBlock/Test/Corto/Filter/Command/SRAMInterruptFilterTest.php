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
use OpenConext\EngineBlockBundle\Configuration\Feature;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration;
use OpenConext\EngineBlockBundle\Exception\InvalidSbsResponseException;
use OpenConext\EngineBlockBundle\Sbs\Dto\AuthzRequest;
use OpenConext\EngineBlockBundle\Sbs\AuthzResponse;
use OpenConext\EngineBlockBundle\Sbs\SbsClientInterface;
use PHPUnit\Framework\TestCase;
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
        $sramFilter = new EngineBlock_Corto_Filter_Command_SRAMInterruptFilter();

        $request = $this->mockRequest();
        $sramFilter->setRequest($request);

        $this->mockFeatureConfiguration(new FeatureConfiguration(['eb.feature_enable_sram_interrupt' => new Feature('eb.feature_enable_sram_interrupt', false)]));

        $sramFilter->execute();
        $this->assertEmpty($sramFilter->getResponseAttributes());
    }

    public function testItDoesNothingIfSpDoesNotHaveCollabEnabled(): void
    {
        $sramFilter = new EngineBlock_Corto_Filter_Command_SRAMInterruptFilter();

        $server = Mockery::mock(EngineBlock_Corto_ProxyServer::class);
        $server->shouldReceive('getRepository')
            ->andReturn($this->repository);

        $sramFilter->setProxyServer($server);

        $request = $this->mockRequest();
        $sramFilter->setRequest($request);

        $this->mockFeatureConfiguration(new FeatureConfiguration(['eb.feature_enable_sram_interrupt' => new Feature('eb.feature_enable_sram_interrupt', true)]));

        /** @var \Mockery\Mock $sbsClient */
        $sbsClient = $this->mockSbsClient();
        $sbsClient->shouldNotReceive('authz');

        $sp = $this->mockServiceProvider('spEntityId');
        $sp->expects('getCoins->collabEnabled')->andReturn(false);

        $sramFilter->setServiceProvider($sp);

        $sramFilter->execute();
        $this->assertEmpty($sramFilter->getResponseAttributes());
    }

    public function testItAddsNonceWhenMessageInterrupt(): void
    {
        $sramFilter = new EngineBlock_Corto_Filter_Command_SRAMInterruptFilter();

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

        $this->mockFeatureConfiguration(new FeatureConfiguration(['eb.feature_enable_sram_interrupt' => new Feature('eb.feature_enable_sram_interrupt', true)]));

        /** @var \Mockery\Mock $sbsClient */
        $sbsClient = $this->mockSbsClient();

        $response = new AuthzResponse();
        $response->msg = 'interrupt';
        $response->nonce = 'hash123';
        $response->attributes = [
            'dummy' => 'attributes',
        ];

        $expectedRequest = new AuthzRequest();
        $expectedRequest->userId = '';
        $expectedRequest->eduPersonPrincipalName = '';
        $expectedRequest->continueUrl = 'https://example.org?ID=';
        $expectedRequest->serviceId = 'spEntityId';
        $expectedRequest->issuerId = 'idpEntityId';

        $sbsClient->shouldReceive('authz')
            ->withArgs(function ($args) use ($expectedRequest) {

                return $args->userId === $expectedRequest->userId
                    && $args->eduPersonPrincipalName === $expectedRequest->eduPersonPrincipalName
                    && strpos($args->continueUrl, $expectedRequest->continueUrl) === 0
                    && $args->serviceId === $expectedRequest->serviceId
                    && $args->issuerId === $expectedRequest->issuerId;
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
        $this->assertSame('hash123', $sramFilter->getResponse()->getSRAMInterruptNonce());
    }

    public function testItAddsSramAttributesOnStatusAuthorized(): void
    {
        $sramFilter = new EngineBlock_Corto_Filter_Command_SRAMInterruptFilter();

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

        $this->mockFeatureConfiguration(new FeatureConfiguration(['eb.feature_enable_sram_interrupt' => new Feature('eb.feature_enable_sram_interrupt', true)]));

        /** @var \Mockery\Mock $sbsClient */
        $sbsClient = $this->mockSbsClient();

        $response = new AuthzResponse();
        $response->msg = 'authorized';
        $response->nonce = 'hash123';
        $response->attributes = [
            'urn:mace:dir:attribute-def:uid' => ['userIdValue'],
            'urn:mace:dir:attribute-def:eduPersonEntitlement' => 'attributes',
        ];

        $expectedRequest = new AuthzRequest();
        $expectedRequest->userId = '';
        $expectedRequest->eduPersonPrincipalName = '';
        $expectedRequest->continueUrl = 'https://example.org?ID=';
        $expectedRequest->serviceId = 'spEntityId';
        $expectedRequest->issuerId = 'idpEntityId';

        $sbsClient->shouldReceive('authz')
            ->withArgs(function ($args) use ($expectedRequest) {

                return $args->userId === $expectedRequest->userId
                    && $args->eduPersonPrincipalName === $expectedRequest->eduPersonPrincipalName
                    && strpos($args->continueUrl, $expectedRequest->continueUrl) === 0
                    && $args->serviceId === $expectedRequest->serviceId
                    && $args->issuerId === $expectedRequest->issuerId;
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
            'urn:mace:dir:attribute-def:eduPersonEntitlement' => 'attributes',
        ];

        $sramFilter->execute();
        $this->assertSame($expectedAttributes, $sramFilter->getResponseAttributes());
        $this->assertSame('', $sramFilter->getResponse()->getSRAMInterruptNonce());
    }

    public function testThrowsEngineBlockExceptionIfPolicyCannotBeChecked()
    {
        $this->expectException(EngineBlock_Exception_SbsCheckFailed::class);
        $this->expectExceptionMessage('The SBS server could not be queried: Server could not be reached.');

        $sbsClient = $this->mockSbsClient();
        $sbsClient->expects('authz')->andThrows(new InvalidSbsResponseException('Server could not be reached.'));

        $sramFilter = new EngineBlock_Corto_Filter_Command_SRAMInterruptFilter();

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

        $this->mockFeatureConfiguration(new FeatureConfiguration(['eb.feature_enable_sram_interrupt' => new Feature('eb.feature_enable_sram_interrupt', true)]));

        /** @var \Mockery\Mock|ServiceProvider $sp */
        $sp = $this->mockServiceProvider('spEntityId');
        $sp->expects('getCoins->collabEnabled')->andReturn(true);
        $sramFilter->setServiceProvider($sp);

        /** @var \Mockery\Mock|IdentityProvider $sp */
        $idp = $this->mockIdentityProvider('idpEntityId');
        $sramFilter->setIdentityProvider($idp);

        $sramFilter->execute();
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

    private function mockSbsClient()
    {
        $sbsClient = Mockery::mock(SbsClientInterface::class);

        /** @var EngineBlock_Application_TestDiContainer $container */
        $container = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $container->setSbsClient($sbsClient);

        return $sbsClient;
    }

    private function mockFeatureConfiguration(FeatureConfiguration $featureConfiguration)
    {
        /** @var EngineBlock_Application_TestDiContainer $container */
        $container = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $container->setFeatureConfiguration($featureConfiguration);
    }

    protected function tearDown(): void
    {
        $container = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $container->setSbsClient(null);

        $container = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();
        $container->setFeatureConfiguration(null);

        parent::tearDown();
    }

}
