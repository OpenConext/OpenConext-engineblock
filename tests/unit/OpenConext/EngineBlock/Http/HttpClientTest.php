<?php

/**
 * This code reuses heavily modified parts of SURFnet/Stepup-Middleware-clientbundle.
 *
 * @see https://github.com/SURFnet/Stepup-Middleware-clientbundle
 *
 * Copyright 2014 SURFnet bv
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

namespace OpenConext\EngineBlock\Http;

use Mockery as m;
use OpenConext\EngineBlock\Http\Exception\AccessDeniedException;
use OpenConext\EngineBlock\Http\Exception\MalformedResponseException;
use OpenConext\EngineBlock\Http\Exception\RequestException;
use PHPUnit_Framework_TestCase as UnitTest;

class HttpClientTest extends UnitTest
{
    /**
     * @test
     * @group EngineBlock
     * @group Http
     */
    public function data_from_a_resource_can_be_read()
    {
        $data     = 'My first resource';
        $response = m::mock('Guzzle\Http\Message\ResponseInterface')
            ->shouldReceive('json')->andReturn($data)
            ->shouldReceive('getStatusCode')->andReturn('200')
            ->getMock();

        $request = m::mock('Guzzle\Http\Message\RequestInterface')
            ->shouldReceive('send')->once()->andReturn($response)
            ->getMock();

        $guzzle = m::mock('Guzzle\Http\ClientInterface')
            ->shouldReceive('get')->with('/resource', null, m::any())->once()->andReturn($request)
            ->getMock();

        $client = new HttpClient($guzzle);

        $this->assertEquals($data, $client->read('/resource'));
    }

    /**
     * @test
     * @group EngineBlock
     * @group http
     */
    public function resource_parameters_are_formatted()
    {
        $response = m::mock('Guzzle\Http\Message\ResponseInterface')
            ->shouldReceive('json')->andReturn('My first resource')
            ->shouldReceive('getStatusCode')->andReturn('200')
            ->getMock();

        $request = m::mock('Guzzle\Http\Message\RequestInterface')
            ->shouldReceive('send')->once()->andReturn($response)
            ->getMock();

        $guzzle = m::mock('Guzzle\Http\ClientInterface')
            ->shouldReceive('get')->with('/resource/John%2FDoe', null, m::any())->once()->andReturn($request)
            ->getMock();

        $httpClient = new HttpClient($guzzle);
        $httpClient->read('/resource/%s', ['John/Doe']);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Http
     */
    public function a_guzzle_request_exception_is_converted_to_an_engineblock_request_exception()
    {
        $request = m::mock('Guzzle\Http\Message\RequestInterface')
            ->shouldReceive('send')->once()->andThrow('Guzzle\Http\Exception\RequestException')
            ->getMock();

        $guzzle = m::mock('Guzzle\Http\ClientInterface')
            ->shouldReceive('get')->with('/some-resource/abc', null, m::any())->once()->andReturn($request)
            ->getMock();

        $client = new HttpClient($guzzle);

        $this->expectException(RequestException::class);
        $client->read('/some-resource/abc');
    }

    /**
     * @test
     * @group EngineBlock
     * @group Http
     */
    public function malformed_json_causes_a_malformed_response_exception()
    {
        $response = m::mock('Guzzle\Http\Message\ResponseInterface')
            ->shouldReceive('json')->andThrow(new \RuntimeException)
            ->shouldReceive('getStatusCode')->andReturn('200')
            ->getMock();

        $request = m::mock('Guzzle\Http\Message\RequestInterface')
            ->shouldReceive('send')->once()->andReturn($response)
            ->getMock();

        $guzzle = m::mock('Guzzle\Http\ClientInterface')
            ->shouldReceive('get')->with('/resource/John%2FDoe', null, m::any())->once()->andReturn($request)
            ->getMock();

        $client = new HttpClient($guzzle);

        $this->expectException(MalformedResponseException::class);
        $client->read('/resource/%s', ['John/Doe']);
    }

    /**
     * @test
     * @group EngineBlock
     * @group Http
     */
    public function null_is_returned_when_the_response_status_code_is_404()
    {
        $response = m::mock('Guzzle\Http\Message\ResponseInterface')
            ->shouldReceive('json')->andReturn([])
            ->shouldReceive('getStatusCode')->andReturn('404')
            ->getMock();

        $request = m::mock('Guzzle\Http\Message\RequestInterface')
            ->shouldReceive('send')->once()->andReturn($response)
            ->getMock();

        $guzzle   = m::mock('Guzzle\Http\ClientInterface')
            ->shouldReceive('get')->with('/some-resource/abc', null, m::any())->once()->andReturn($request)
            ->getMock();

        $client  = new HttpClient($guzzle);

        $this->assertNull(
            $client->read('/some-resource/abc'),
            'Resource does not exist, yet a non-null value was returned'
        );
    }

    /**
     * @test
     * @group EngineBlock
     * @group Http
     */
    public function an_access_denied_exception_is_thrown_if_the_response_status_code_is_403()
    {
        $response = m::mock('Guzzle\Http\Message\ResponseInterface')
            ->shouldReceive('json')->andReturn([])
            ->shouldReceive('getStatusCode')->andReturn('403')
            ->getMock();

        $request = m::mock('Guzzle\Http\Message\RequestInterface')
            ->shouldReceive('send')->once()->andReturn($response)
            ->getMock();


        $guzzle = m::mock('Guzzle\Http\ClientInterface')
            ->shouldReceive('get')->with('/some-resource/abc', null, m::any())->once()->andReturn($request)
            ->getMock();

        $client = new HttpClient($guzzle);

        $this->expectException(AccessDeniedException::class);
        $client->read('/some-resource/abc');
    }
}
