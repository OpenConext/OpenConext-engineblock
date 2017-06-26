<?php

/**
 * Copyright 2017 SURFnet B.V.
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

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use OpenConext\Component\EngineBlockMetadata\AttributeReleasePolicy;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlockBundle\AttributeAggregation\AttributeAggregationClientInterface;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\Response;
use OpenConext\EngineBlock\Http\Exception\HttpException;
use PHPUnit_Framework_TestCase as UnitTest;

/**
 * @group AttributeAggregation
 */
class EngineBlock_Test_Corto_Filter_Command_AttributeAggregatorTest extends UnitTest
{
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

    public function setUp()
    {
        $this->handler = new TestHandler();
        $this->logger  = new Logger('Test', array($this->handler));

        $this->sp = new ServiceProvider('SP');


        $this->repository = Mockery::mock(MetadataRepositoryInterface::class);
        $this->repository->shouldReceive('findServiceProviderByEntityId')
            ->andReturn($this->sp);

        $assertion = new SAML2_Assertion();

        $request = new SAML2_AuthnRequest();
        $response = new SAML2_Response();
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

        $command = new EngineBlock_Corto_Filter_Command_AttributeAggregator($this->logger, $client, $this->repository);
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

        $this->repository->shouldReceive('fetchServiceProviderArp')
            ->andReturn(new AttributeReleasePolicy([]));

        $this->sp->attributeAggregationRequired = true;

        $command = new EngineBlock_Corto_Filter_Command_AttributeAggregator($this->logger, $client, $this->repository);
        $command->setProxyServer($server);
        $command->setRequest($this->request);
        $command->setResponse($this->response);
        $command->setServiceProvider($this->sp);

        $command->execute();

        $aggregationNotConfiguredLogged = $this->handler->hasWarning(
            'No attribute rules configured for aggregation for SP'
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

        $this->repository->shouldReceive('fetchServiceProviderArp')
            ->andReturn(new AttributeReleasePolicy([
                'name' => [
                    [
                        'value' => 'value',
                        'source' => 'source',
                    ],
                ],
            ]));

        $this->sp->attributeAggregationRequired = true;

        $command = new EngineBlock_Corto_Filter_Command_AttributeAggregator($this->logger, $client, $this->repository);
        $command->setCollabPersonId('subjectId');
        $command->setProxyServer($server);
        $command->setRequest($this->request);
        $command->setResponse($this->response);
        $command->setServiceProvider($this->sp);

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

        $this->repository->shouldReceive('fetchServiceProviderArp')
            ->andReturn(new AttributeReleasePolicy([
                'name' => [
                    [
                        'value' => 'value',
                        'source' => 'source',
                    ],
                ],
            ]));

        $this->sp->attributeAggregationRequired = true;

        $command = new EngineBlock_Corto_Filter_Command_AttributeAggregator($this->logger, $client, $this->repository);
        $command->setCollabPersonId('subjectId');
        $command->setProxyServer($server);
        $command->setRequest($this->request);
        $command->setResponse($this->response);
        $command->setServiceProvider($this->sp);

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

        $this->repository->shouldReceive('fetchServiceProviderArp')
            ->andReturn(new AttributeReleasePolicy([
                'name' => [
                    [
                        'value' => 'value',
                        'source' => 'source',
                    ],
                ],
            ]));

        $this->sp->attributeAggregationRequired = true;

        $command = new EngineBlock_Corto_Filter_Command_AttributeAggregator($this->logger, $client, $this->repository);
        $command->setCollabPersonId('subjectId');
        $command->setProxyServer($server);
        $command->setRequest($this->request);
        $command->setResponse($this->response);
        $command->setResponseAttributes([
          'name' => ['non-aggregated-value'],
        ]);
        $command->setServiceProvider($this->sp);

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

        $this->repository->shouldReceive('fetchServiceProviderArp')
            ->andReturn(new AttributeReleasePolicy([
                'name' => [
                    [
                        'value' => 'value',
                        'source' => 'source',
                    ],
                ],
            ]));

        $this->sp->attributeAggregationRequired = true;

        $command = new EngineBlock_Corto_Filter_Command_AttributeAggregator($this->logger, $client, $this->repository);
        $command->setCollabPersonId('subjectId');
        $command->setProxyServer($server);
        $command->setRequest($this->request);
        $command->setResponse($this->response);
        $command->setResponseAttributes([
          'name' => ['non-aggregated-value'],
        ]);

        $command->setServiceProvider($this->sp);

        $command->execute();

        // The 'name' attribute should not be there, because it is configured
        // for aggregation and the aggregator returned no results. The
        // attribute was in the response, but since the ARP specifies a source
        // and the source didn't return the attribute, the attribute should be
        // dropped.
        $this->assertEquals([], $command->getResponseAttributes());
    }

}
