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

namespace OpenConext\EngineBlock\Validator;

use OpenConext\EngineBlock\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Valid requests method / query parameter combinations for IdP initiated SSO (unsolicited SSO) are:
 *
 *  | Request method | Parameter name  |
 *  | -------------- | --------------- |
 *  | GET            | sp-entity-id    |
 */
class UnsolicitedSsoRequestValidator implements RequestValidator
{
    private $supportedRequestMethods = [Request::METHOD_GET];

    public function isValid(Request $request)
    {
        $requestMethod = $request->getMethod();
        // Defense in depth; anything other than GET is probably already rejected at routing time.
        if (!in_array($requestMethod, $this->supportedRequestMethods)) {
            throw new RuntimeException(
                sprintf(
                    'The HTTP request method "%s" is not supported on the IdP initiated SSO endpoint',
                    $requestMethod
                )
            );
        }

        if (!$request->query->has('sp-entity-id')) {
            throw new RuntimeException(
                sprintf('The query parameter "sp-entity-id" is missing on the IdP initiated SSO request')
            );
        }

        return true;
    }
}
