<?php

/**
 * This code reuses modified parts of SURFnet/Stepup-Middleware-clientbundle.
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

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Exception\RequestException as GuzzleRequestException;
use OpenConext\EngineBlock\Exception\RuntimeException;
use OpenConext\EngineBlock\Http\Exception\AccessDeniedException;
use OpenConext\EngineBlock\Http\Exception\MalformedResponseException;
use OpenConext\EngineBlock\Http\Exception\RequestException;
use OpenConext\EngineBlock\Http\Exception\UnreadableResourceException;
use RuntimeException as CoreRuntimeException;

class HttpClient
{
    /**
     * @var ClientInterface
     */
    private $guzzleClient;

    /**
     * @param ClientInterface $guzzleClient
     */
    public function __construct(ClientInterface $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @param string         $path       A URL path, optionally containing printf parameters (e.g. '/a/b/%s/d'). The
     *                                   parameters will be URL encoded and formatted into the path string.
     *                                   Example: '/foo/%s/bar/%s', ['foo' => 'ab-cd', 'bar' => 'ef']
     * @param array          $parameters An array containing the parameters to replace in the path.
     * @param HttpQuery|null $httpQuery
     * @return null|mixed Most likely an array structure, null when the resource doesn't exist.
     * @throws MalformedResponseException When the server doesn't respond with (well-formed) JSON.
     * @throws AccessDeniedException When the consumer isn't authorised to access given resource.
     * @throws UnreadableResourceException When the server doesn't respond with the resource.
     */
    public function read($path, array $parameters = array(), HttpQuery $httpQuery = null)
    {
        $resource = $this->buildResourcePath($path, $parameters, $httpQuery);

        try {
            $response = $this->guzzleClient
                ->get($resource, null, array('exceptions' => false))
                ->send();

            $statusCode = $response->getStatusCode();

            $data = $response->json();
        } catch (GuzzleRequestException $exception) {
            throw new RequestException(
                sprintf(
                    'Could not send request to resource "%s": "%s"',
                    $resource,
                    $exception->getMessage()
                ),
                $exception
            );
        } catch (CoreRuntimeException $exception) {
            // Malformed JSON body
            throw new MalformedResponseException(
                sprintf('Cannot read resource: "%s"', $exception->getMessage()),
                $exception
            );
        }

        // 404 is considered a valid response, the resource may not be there (yet?) intentionally.
        if ($statusCode == 404) {
            return null;
        }

        if ($statusCode == 403) {
            throw new AccessDeniedException($resource);
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new UnreadableResourceException(sprintf('Resource could not be read (status code %d)', $statusCode));
        }

        return $data;
    }

    /**
     * @param string         $path
     * @param array          $parameters
     * @param HttpQuery|null $httpQuery
     * @return string
     */
    private function buildResourcePath($path, array $parameters, HttpQuery $httpQuery = null)
    {
        if (count($parameters) > 0) {
            $resource = vsprintf($path, array_map('urlencode', $parameters));
        } else {
            $resource = $path;
        }

        if (empty($resource)) {
            throw new RuntimeException(sprintf(
                'Could not construct resource path from path "%s", parameters "%s" and search query "%s"',
                $path,
                implode('","', $parameters),
                $httpQuery ? $httpQuery->toHttpQuery() : ''
            ));
        }

        if ($httpQuery !== null) {
            $resource .= $httpQuery->toHttpQuery();
        }

        return $resource;
    }
}
