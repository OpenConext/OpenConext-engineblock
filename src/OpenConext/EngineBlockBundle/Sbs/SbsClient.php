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

    public function __construct(
        private readonly HttpClient $httpClient,
        private readonly string $sbsBaseUrl,
        private readonly string $authzLocation,
        private readonly string $attributesLocation,
        private readonly string $interruptLocation,
        private readonly string $apiToken,
        private readonly bool $verifyPeer
    ) {
    }

    public function authz(AuthzRequest $request): AuthzResponse
    {
        $jsonData = $this->httpClient->post(
            json_encode($request, JSON_THROW_ON_ERROR),
            $this->buildUrl($this->authzLocation),
            [],
            $this->requestHeaders(),
            $this->verifyPeer
        );

        if (!is_array($jsonData)) {
            throw new InvalidSbsResponseException('Received non-array from SBS server: ' . get_debug_type($jsonData));
        }

        return AuthzResponse::fromData($jsonData);
    }

    public function requestAttributesFor(AttributesRequest $request): AttributesResponse
    {
        $jsonData = $this->httpClient->post(
            json_encode($request, JSON_THROW_ON_ERROR),
            $this->buildUrl($this->attributesLocation),
            [],
            $this->requestHeaders(),
            $this->verifyPeer
        );

        if (!is_array($jsonData)) {
            throw new InvalidSbsResponseException('Received non-array from SBS server: ' . get_debug_type($jsonData));
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
        return $this->buildUrl($this->interruptLocation) . "?nonce=" . $nonce;
    }

    private function buildUrl(string $path): string
    {
        $baseUrl = rtrim($this->sbsBaseUrl, '/');
        $path = '/' . ltrim($path, '/');
        return $baseUrl . $path;
    }
}
