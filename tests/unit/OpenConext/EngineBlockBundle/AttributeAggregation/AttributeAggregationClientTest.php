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

namespace OpenConext\EngineBlockBundle\Tests\AttributeAggregation;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Mockery as m;
use OpenConext\EngineBlockBundle\AttributeAggregation\AttributeAggregationClient;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\AttributeRule;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\Request;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\Response;
use OpenConext\EngineBlock\Http\HttpClient;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @group AttributeAggregation
 */
class AttributeAggregationClientTest extends TestCase
{
    /**
     * @test
     */
    public function an_attributeaggregation_client_parses_the_aggregator_response()
    {
        $request = Request::from(
          'subject',
          [],
          [
            AttributeRule::from('name', 'value', 'source'),
            AttributeRule::from('name', 'value', 'source'),
          ]
        );

        $responseFixture = file_get_contents(__DIR__ . '/fixture/success-response.json');

        $mockHandler = new MockHandler([
            new GuzzleResponse(200, [], $responseFixture)
        ]);

        $guzzle = new Client(['handler' => $mockHandler]);

        $client = new AttributeAggregationClient(new HttpClient($guzzle), 'https://attr.at/api');
        $response = $client->aggregate($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertCount(2, $response->attributes);
        $this->assertEquals('urn:mace:dir:attribute-def:eduPersonEntitlement', $response->attributes[0]->name);
        $this->assertEquals('urn:x-surfnet:surfnet.nl:sab:role:SURFwireless-beheerder', $response->attributes[0]->values[0]);
        $this->assertEquals('urn:x-surfnet:surfnet.nl:sab:role:Coordinerend-SURF-Contactpersoon', $response->attributes[0]->values[1]);
        $this->assertEquals('sab', $response->attributes[0]->source);
    }
}
