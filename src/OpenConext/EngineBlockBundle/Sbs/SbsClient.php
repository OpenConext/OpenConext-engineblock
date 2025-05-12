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
use OpenConext\EngineBlockBundle\Exception\InvalidSbsResponseException;
use OpenConext\EngineBlockBundle\Sbs\Dto\AttributesRequest;
use OpenConext\EngineBlockBundle\Sbs\Dto\AuthzRequest;

final class SbsClient implements SbsClientInterface
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
    private $apiToken;

    /**
     * @var string
     */
    private $sbsBaseUrl;

    /**
     * @var string
     */
    private $authzLocation;

    /**
     * @var string
     */
    private $attributesLocation;

    /**
     * @var bool
     */
    private $verifyPeer;


    public function __construct(
        HttpClient $httpClient,
        string $sbsBaseUrl,
        string $authzLocation,
        string $attributesLocation,
        string $interruptLocation,
        string $apiToken,
        bool $verifyPeer
    ) {
        $this->httpClient = $httpClient;
        $this->sbsBaseUrl = $sbsBaseUrl;
        $this->authzLocation = $authzLocation;
        $this->attributesLocation = $attributesLocation;
        $this->interruptLocation = $interruptLocation;
        $this->apiToken = $apiToken;
        $this->verifyPeer = $verifyPeer;
    }

    public function authz(AuthzRequest $request): AuthzResponse
    {
        $jsonData = $this->httpClient->post(
            json_encode($request),
            $this->authzLocation,
            [],
            $this->requestHeaders(),
            $this->verifyPeer
        );

        if (!is_array($jsonData)) {
            throw new InvalidSbsResponseException('Received non-array from SBS server: ' . var_export($jsonData, true));
        }

        return AuthzResponse::fromData($jsonData);
    }

    // Attributes use authzLocation !!
    public function requestAttributesFor(AttributesRequest $request): AttributesResponse
    {
        $jsonData = $this->httpClient->post(
            json_encode($request),
            $this->attributesLocation,
            [],
            $this->requestHeaders(),
            $this->verifyPeer
        );

        if (!is_array($jsonData)) {
            throw new InvalidSbsResponseException('Received non-array from SBS server: ' . var_export($jsonData, true));
        }

        return AttributesResponse::fromData($jsonData);
    }

    private function requestHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => $this->apiToken,
        ];
    }

    public function getInterruptLocationLink(string $nonce): string
    {
        return $this->sbsBaseUrl . $this->interruptLocation . "?nonce=$nonce";
    }
}
