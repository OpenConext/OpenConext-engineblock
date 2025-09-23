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

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use OpenConext\EngineBlock\Http\Exception\AccessDeniedException;
use OpenConext\EngineBlock\Http\Exception\MalformedResponseException;
use PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Http')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function data_from_a_resource_can_be_read()
    {
        $data = 'My first resource';

        $mockHandler = new MockHandler(
            [
                new Response(200, [], json_encode($data))
            ]
        );
        $guzzle      = new Client(['handler' => $mockHandler]);
        $client      = new HttpClient($guzzle);

        $response = $client->read('/give-me/resource');

        $this->assertEquals($data, $response);
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Http')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function malformed_json_causes_a_malformed_response_exception_when_reading()
    {
        $malformedJson = '{';

        $mockHandler   = new MockHandler([
            new Response(200, [], $malformedJson)
        ]);
        $guzzle = new Client(['handler' => $mockHandler]);
        $client = new HttpClient($guzzle);

        $this->expectException(MalformedResponseException::class);

        $client->read('/give-me/malformed-json');
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Http')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function null_is_returned_when_the_response_status_code_is_404_when_reading()
    {
        $mockHandler   = new MockHandler([
            new Response(404, [])
        ]);
        $guzzle = new Client(['handler' => $mockHandler]);
        $client = new HttpClient($guzzle);

        $response = $client->read('give-me/404');

        $this->assertNull(
            $response,
            'The response should be null when encountering a 404 when reading, but it was not'
        );
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Http')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function an_access_denied_exception_is_thrown_if_the_response_status_code_is_403_when_reading()
    {
        $mockHandler   = new MockHandler([
            new Response(403, [])
        ]);
        $guzzle = new Client(['handler' => $mockHandler]);
        $client = new HttpClient($guzzle);

        $this->expectException(AccessDeniedException::class);

        $client->read('give-me/403');
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Http')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function data_from_a_resource_can_be_posted()
    {
        $data     = 'Received data';
        $mockHandler = new MockHandler(
            [
                new Response(200, [], json_encode($data))
            ]
        );
        $guzzle      = new Client(['handler' => $mockHandler]);
        $client      = new HttpClient($guzzle);

        $response = $client->post('/resource', 'Post body');

        $this->assertEquals($data, $response);
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Http')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function malformed_json_causes_a_malformed_response_exception_when_posting()
    {
        $malformedJson = '{';

        $mockHandler   = new MockHandler([
            new Response(200, [], $malformedJson)
        ]);
        $guzzle = new Client(['handler' => $mockHandler]);
        $client = new HttpClient($guzzle);

        $this->expectException(MalformedResponseException::class);

        $client->post('/post-and-give-me/malformed-json', 'Post body');
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Http')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function null_is_returned_when_the_response_status_code_is_404_when_posting()
    {
        $mockHandler   = new MockHandler([
            new Response(404, [])
        ]);
        $guzzle = new Client(['handler' => $mockHandler]);
        $client = new HttpClient($guzzle);

        $response = $client->post('post-and-give-me/404', 'Post body');

        $this->assertNull(
            $response,
            'The response should be null when encountering a 404 when reading, but it was not'
        );
    }

    #[\PHPUnit\Framework\Attributes\Group('EngineBlock')]
    #[\PHPUnit\Framework\Attributes\Group('Http')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function an_access_denied_exception_is_thrown_if_the_response_status_code_is_403_when_posting()
    {
        $mockHandler   = new MockHandler([
            new Response(403, [])
        ]);
        $guzzle = new Client(['handler' => $mockHandler]);
        $client = new HttpClient($guzzle);

        $this->expectException(AccessDeniedException::class);

        $client->post('post-and-give-me/403', 'Post body');
    }
}
