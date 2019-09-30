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

namespace OpenConext\EngineBlockBridge;

use EngineBlock_Http_Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ResponseFactory
{
    /**
     * @param EngineBlock_Http_Response $response
     * @return Response
     */
    public static function fromEngineBlockResponse(EngineBlock_Http_Response $response)
    {
        if ($response->getRedirectUrl()) {
            return new RedirectResponse($response->getRedirectUrl());
        }

        return new Response($response->getBody(), $response->getStatusCode(), $response->getHeaders());
    }
}
