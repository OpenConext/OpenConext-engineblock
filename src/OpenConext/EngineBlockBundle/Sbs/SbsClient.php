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

namespace OpenConext\EngineBlockBundle\Sbs;

use OpenConext\EngineBlock\Http\HttpClient;
use OpenConext\EngineBlockBundle\Pdp\Dto\Request;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response;
use OpenConext\EngineBlockBundle\Pdp\PdpClientInterface;
use OpenConext\EngineBlockBundle\Pdp\PolicyDecision;

final class SbsClient implements PdpClientInterface
{
    /**
     * @var HttpClient
     */
    private $httpClient;
    /**
     * @var string
     */
    private $interruptLocation;
    /**
     * @var string
     */
    private $entitlementsLocation;


    public function __construct(
        HttpClient $httpClient,
        string $interruptLocation,
        string $entitlementsLocation
    ) {
        $this->httpClient              = $httpClient;
        $this->interruptLocation = $interruptLocation;
        $this->entitlementsLocation = $entitlementsLocation;
    }

    public function requestInterruptDecisionFor(Request $request) : PolicyDecision
    {
        $jsonData = $this->httpClient->post(
            json_encode($request),
            $this->interruptLocation,
            [],
            [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ]
        );
        $response = Response::fromData($jsonData);

        return PolicyDecision::fromResponse($response);
    }
    public function requestEntitlementsFor(Request $request) : PolicyDecision
    {
        $jsonData = $this->httpClient->post(
            json_encode($request),
            $this->entitlementsLocation,
            [],
            [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ]
        );
        $response = Response::fromData($jsonData);

        return PolicyDecision::fromResponse($response);
    }
}
