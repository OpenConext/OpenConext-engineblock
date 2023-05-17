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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlockBundle\AttributeAggregation\AttributeAggregationClientInterface;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\Response;
use OpenConext\EngineBlock\Http\Exception\HttpException;
use PHPUnit\Framework\TestCase;
use SAML2\Assertion;
use SAML2\AuthnRequest;
use SAML2\Response as SAMLResponse;

/**
 * @group AttributeAggregation
 */
class EngineBlock_Test_Corto_Filter_Command_AttributeAggregatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var TestHandler
     */
    private $handler;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ServiceProvider
     */
    private $sp;

    /**
     * @var IdentityProvider
     */
    private $idp;

    /**
     * @var MetadataRepositoryInterface
     */
    private $repository;

    /**
     * @var EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    private $request;

    /**
     * @var EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    private $response;

    public function setUp(): void
    {
        $this->handler = new TestHandler();
        $this->logger  = new Logger('Test', array($this->handler));

        $this->sp = new ServiceProvider('SP');
        $this->idp = new IdentityProvider('IdP');

        $this->repository = Mockery::mock(MetadataRepositoryInterface::class);
        $this->repository->shouldReceive('findServiceProviderByEntityId')
            ->andReturn($this->sp);

        $assertion = new Assertion();

        $request = new AuthnRequest();
        $response = new SAMLResponse();
        $response->setAssertions(array($assertion));

        $this->request = new EngineBlock_Saml2_AuthnRequestAnnotationDecorator($request);
        $this->response = new EngineBlock_Saml2_ResponseAnnotationDecorator($response);
    }

    public function testAggregatorNotCalledWhenAggregationIsDisabled()
    {
        $client = Mockery::mock(AttributeAggregationClientInterface::class);
        $client->shouldNotReceive('aggregate');

        $server = Mockery::mock(EngineBlock_Corto_ProxyServer::class);
        $server->shouldReceive('getRepository')
            ->andReturn($this->repository);

        $command = new EngineBlock_Corto_Filter_Command_AttributeAggregator($this->logger, $client);
        $command->setProxyServer($server);
        $command->setRequest($this->request);
        $command->setResponse($this->response);
        $command->setServiceProvider($this->sp);

        $command->execute();

        $aggregationDisabledLogged = $this->handler->hasInfo(
            'No Attribute Aggregation for SP'
        );

        $this->assertTrue($aggregationDisabledLogged, 'Logging that coin:attribute_aggregation_required is disabled');
    }

    public function testAggregatorNotCalledWhenNoAttributesConfigured()
    {
        $client = Mockery::mock(AttributeAggregationClientInterface::class);
        $client->shouldNotReceive('aggregate');

        $server = Mockery::mock(EngineBlock_Corto_ProxyServer::class);
        $server->shouldReceive('getRepository')
            ->andReturn($this->repository);

        $this->sp->attributeReleasePolicy = new AttributeReleasePolicy([]);

        $command = new EngineBlock_Corto_Filter_Command_AttributeAggregator($this->logger, $client);
        $command->setProxyServer($server);
        $command->setRequest($this->request);
        $command->setResponse($this->response);
        $command->setServiceProvider($this->sp);

        $command->execute();

        $aggregationNotConfiguredLogged = $this->handler->hasInfo(
            'No Attribute Aggregation for SP'
        );

        $this->assertTrue($aggregationNotConfiguredLogged, 'Logging that no attributes are configured in ARP for aggregation');
    }

    public function testAggregatorIsCalledWhenArpCorrectlyConfigured()
    {
        $client = Mockery::mock(AttributeAggregationClientInterface::class);
        $client->shouldReceive('aggregate')
            ->andReturn(Response::fromData([]));

        $server = Mockery::mock(EngineBlock_Corto_ProxyServer::class);
        $server->shouldReceive('getRepository')
            ->andReturn($this->repository);

        $this->sp->attributeReleasePolicy = new AttributeReleasePolicy([
            'name' => [
                [
                    'value' => 'value',
                    'source' => 'source',
                ],
            ],
        ]);

        $command = new EngineBlock_Corto_Filter_Command_AttributeAggregator($this->logger, $client);
        $command->setCollabPersonId('subjectId');
        $command->setProxyServer($server);
        $command->setRequest($this->request);
        $command->setResponse($this->response);
        $command->setServiceProvider($this->sp);
        $command->setIdentityProvider($this->idp);
        $command->execute();
    }

    public function testAggregatorHandlesHttpClientExceptions()
    {
        $client = Mockery::mock(AttributeAggregationClientInterface::class);
        $client->shouldReceive('aggregate')
            ->andThrow(HttpException::class);

        $server = Mockery::mock(EngineBlock_Corto_ProxyServer::class);
        $server->shouldReceive('getRepository')
            ->andReturn($this->repository);

        $this->sp->attributeReleasePolicy = new AttributeReleasePolicy([
            'name' => [
                [
                    'value' => 'value',
                    'source' => 'source',
                ],
            ],
        ]);

        $command = new EngineBlock_Corto_Filter_Command_AttributeAggregator($this->logger, $client);
        $command->setCollabPersonId('subjectId');
        $command->setProxyServer($server);
        $command->setRequest($this->request);
        $command->setResponse($this->response);
        $command->setServiceProvider($this->sp);
        $command->setIdentityProvider($this->idp);

        $command->execute();

        $exceptionLogged = $this->handler->hasError(
            'Error accessing the attribute aggregator API endpoint for SP'
        );

        $this->assertTrue($exceptionLogged, 'HTTP exception on the AA endpoint is logged');
    }

    public function testAggregatorReplacesAttributes()
    {
        $client = Mockery::mock(AttributeAggregationClientInterface::class);
        $client->shouldReceive('aggregate')
            ->andReturn(Response::fromData([
                [
                    'name' => 'name',
                    'values' => ['aggregated-value'],
                    'source' => 'source',
                ],
            ]));

        $server = Mockery::mock(EngineBlock_Corto_ProxyServer::class);
        $server->shouldReceive('getRepository')
            ->andReturn($this->repository);

        $this->sp->attributeReleasePolicy = new AttributeReleasePolicy([
            'name' => [
                [
                    'value' => 'value',
                    'source' => 'source',
                ],
            ],
        ]);

        $command = new EngineBlock_Corto_Filter_Command_AttributeAggregator($this->logger, $client);
        $command->setCollabPersonId('subjectId');
        $command->setProxyServer($server);
        $command->setRequest($this->request);
        $command->setResponse($this->response);
        $command->setResponseAttributes([
          'name' => ['non-aggregated-value'],
        ]);
        $command->setServiceProvider($this->sp);
        $command->setIdentityProvider($this->idp);

        $command->execute();

        $this->assertEquals(
            [
                'name' => [
                    'aggregated-value',
                ],
            ],
            $command->getResponseAttributes()
        );

        $this->assertEquals(
            [
                'name' => 'source'
            ],
            $command->getResponseAttributeSources()
        );
    }

    public function testAggregatorStripsIdpAttributesIfAggregatorHasNoResults()
    {
        $client = Mockery::mock(AttributeAggregationClientInterface::class);
        $client->shouldReceive('aggregate')
            ->andReturn(Response::fromData([]));

        $server = Mockery::mock(EngineBlock_Corto_ProxyServer::class);
        $server->shouldReceive('getRepository')
            ->andReturn($this->repository);

        $this->sp->attributeReleasePolicy = new AttributeReleasePolicy([
            'name' => [
                [
                    'value' => 'value',
                    'source' => 'source',
                ],
            ],
        ]);

        $command = new EngineBlock_Corto_Filter_Command_AttributeAggregator($this->logger, $client);
        $command->setCollabPersonId('subjectId');
        $command->setProxyServer($server);
        $command->setRequest($this->request);
        $command->setResponse($this->response);
        $command->setResponseAttributes([
          'name' => ['non-aggregated-value'],
        ]);

        $command->setServiceProvider($this->sp);
        $command->setIdentityProvider($this->idp);

        $command->execute();

        // The 'name' attribute should not be there, because it is configured
        // for aggregation and the aggregator returned no results. The
        // attribute was in the response, but since the ARP specifies a source
        // and the source didn't return the attribute, the attribute should be
        // dropped.
        $this->assertEquals([], $command->getResponseAttributes());
    }

    public function testAggregatorCalledWhenThereAreNonIdpSources()
    {
        $client = Mockery::mock(AttributeAggregationClientInterface::class);
        $client->shouldReceive('aggregate')
            ->andReturn(Response::fromData([]));

        $server = Mockery::mock(EngineBlock_Corto_ProxyServer::class);
        $server->shouldReceive('getRepository')
            ->andReturn($this->repository);

        $this->sp->attributeReleasePolicy = new AttributeReleasePolicy([
            'name' => [
                [
                    'value' => 'value',
                    'source' => 'non-idp',
                ],
            ],
        ]);

        $command = new EngineBlock_Corto_Filter_Command_AttributeAggregator($this->logger, $client);
        $command->setCollabPersonId('subjectId');
        $command->setProxyServer($server);
        $command->setRequest($this->request);
        $command->setResponse($this->response);
        $command->setServiceProvider($this->sp);
        $command->setIdentityProvider($this->idp);

        $command->execute();
    }

    public function testAggregatorNotCalledWhenThereAreIdpSources()
    {
        $client = Mockery::mock(AttributeAggregationClientInterface::class);
        $client->shouldNotReceive('aggregate');

        $server = Mockery::mock(EngineBlock_Corto_ProxyServer::class);
        $server->shouldReceive('getRepository')
            ->andReturn($this->repository);

        $this->sp->attributeReleasePolicy = new AttributeReleasePolicy([
            'name' => [
                [
                    'value' => 'value',
                    'source' => 'idp',
                ],
            ],
        ]);

        $command = new EngineBlock_Corto_Filter_Command_AttributeAggregator($this->logger, $client);
        $command->setProxyServer($server);
        $command->setRequest($this->request);
        $command->setResponse($this->response);
        $command->setServiceProvider($this->sp);
        $command->setIdentityProvider($this->idp);

        $command->execute();

        $aggregationNotConfiguredLogged = $this->handler->hasInfo(
            'No Attribute Aggregation for SP'
        );

        $this->assertTrue($aggregationNotConfiguredLogged, 'Logging that there are no idp sources ARP for aggregation');
    }
}
