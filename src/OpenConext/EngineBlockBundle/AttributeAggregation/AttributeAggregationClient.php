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

namespace OpenConext\EngineBlockBundle\AttributeAggregation;

use OpenConext\EngineBlock\Http\HttpClient;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\Request;
use OpenConext\EngineBlockBundle\AttributeAggregation\Dto\Response;

final class AttributeAggregationClient implements AttributeAggregationClientInterface
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var string
     */
    private $apiBasePath;

    public function __construct(HttpClient $httpClient, $apiBasePath)
    {
        $this->httpClient = $httpClient;
        $this->apiBasePath = $apiBasePath;
    }

    /**
     * Get aggregations.
     *
     * @param Request $request
     * @return Response
     * @throws \OpenConext\EngineBlock\Http\Exception\HttpException
     */
    public function aggregate(Request $request)
    {
        $jsonData = $this->httpClient->post(
            json_encode($request),
            $this->apiBasePath,
            [],
            [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ]
        );

        return Response::fromData($jsonData);
    }
}
