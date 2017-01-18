<?php

/**
 * Copyright 2016 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Pdp;

use OpenConext\EngineBlock\Http\HttpClient;
use OpenConext\EngineBlockBundle\Pdp\Dto\Request;
use OpenConext\EngineBlockBundle\Pdp\Dto\Response;

final class PdpClient
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param Request $request
     * @return PolicyDecision $policyDecision
     */
    public function giveDecisionBasedOn(Request $request)
    {
        $jsonData = $this->httpClient->post(
            'decide/policy',
            json_encode($request),
            [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ]
        );
        $response = Response::fromData($jsonData);

        return PolicyDecision::fromResponse($response);
    }
}
