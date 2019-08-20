<?php

/**
 * Copyright 2014 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Security\Http\EntryPoint;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * JsonBasicAuthenticationEntryPoint starts an HTTP Basic authentication with a JSON response body.
 */
class JsonBasicAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    private $realmName;

    public function __construct($realmName)
    {
        $this->realmName = $realmName;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $authExceptionMessage = $authException ? $authException->getMessage() : '';
        $error                = sprintf(
            'You are required to authorise before accessing this API (%s).',
            $authExceptionMessage
        );
        $response             = new JsonResponse(
            ['errors' => [$error]],
            401,
            ['WWW-Authenticate' => sprintf('Basic realm="%s"', $this->realmName)]
        );

        return $response;
    }
}
