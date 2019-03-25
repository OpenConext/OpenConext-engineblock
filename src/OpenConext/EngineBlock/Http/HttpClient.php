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

namespace OpenConext\EngineBlock\Http;

use GuzzleHttp\ClientInterface;
use OpenConext\EngineBlock\Exception\InvalidJsonException;
use OpenConext\EngineBlock\Http\Exception\AccessDeniedException;
use OpenConext\EngineBlock\Http\Exception\MalformedResponseException;
use OpenConext\EngineBlock\Http\Exception\UnreadableResourceException;

final class HttpClient
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @param ClientInterface $httpClient
     */
    public function __construct(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param string $path A URL path, optionally containing printf parameters. The parameters
     *               will be URL encoded and formatted into the path string.
     *               Example: "connections/%d.json"
     * @param array  $parameters
     * @param array  $headers
     * @return mixed $data
     * @throws AccessDeniedException
     * @throws UnreadableResourceException
     * @throws MalformedResponseException
     */
    public function read($path, array $parameters = [], array $headers = [])
    {
        $resource = ResourcePathFormatter::format($path, $parameters);
        $response = $this->httpClient->request('GET', $resource, [
            'exceptions' => false,
            'headers' => $headers
        ]);
        $statusCode = $response->getStatusCode();

        // 404 is considered a valid response, the resource may not be there (yet?) intentionally.
        if ($statusCode == 404) {
            return null;
        }

        if ($statusCode == 403) {
            throw new AccessDeniedException($resource);
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new UnreadableResourceException(sprintf('Resource could not be read (status code "%d")', $statusCode));
        }

        try {
            $data = JsonResponseParser::parse((string) $response->getBody());
        } catch (InvalidJsonException $e) {
            throw new MalformedResponseException(
                sprintf('Cannot read resource "%s": malformed JSON returned', $resource)
            );
        }

        return $data;
    }

    /**
     * @param mixed $data
     * @param string $path
     * @param array $parameters
     * @param array $headers
     * @return mixed
     */
    public function post($data, $path, $parameters = [], array $headers = [])
    {
        $resource = ResourcePathFormatter::format($path, $parameters);
        $response = $this->httpClient->request('POST', $resource, [
            'exceptions' => false,
            'body' => $data,
            'headers' => $headers
        ]);
        $statusCode = $response->getStatusCode();

        // 404 is considered a valid response, the resource may not be there (yet?) intentionally.
        if ($statusCode == 404) {
            return null;
        }

        if ($statusCode == 403) {
            throw new AccessDeniedException($resource);
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new UnreadableResourceException(sprintf('Resource could not be read (status code "%d")', $statusCode));
        }

        try {
            $data = JsonResponseParser::parse((string) $response->getBody());
        } catch (InvalidJsonException $e) {
            throw new MalformedResponseException(
                sprintf('Cannot read resource "%s": malformed JSON returned', $resource)
            );
        }

        return $data;
    }
}
