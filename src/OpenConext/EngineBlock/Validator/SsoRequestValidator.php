<?php

/**
 * Copyright 2019 SURFnet B.V.
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

use OpenConext\EngineBlock\Exception\InvalidRequestMethodException;
use OpenConext\EngineBlock\Exception\MissingParameterException;
use Symfony\Component\HttpFoundation\Request;

/**
 * The SsoRequestValidator verifies valid saml binding
 *
 * Valid requests method / binding combinations for SSO are:
 *
 *  | Request method | Parameter name  |
 *  | -------------- | --------------- |
 *  | GET            | SAMLRequest     |
 *  | POST           | SAMLRequest    |
 */
class SsoRequestValidator implements RequestValidator
{
    private $supportedRequestMethods = [Request::METHOD_GET, Request::METHOD_POST];

    public function isValid(Request $request)
    {
        $requestMethod = $request->getMethod();
        // Defense in depth; anything other than POST and GET are probably already rejected at routing time.
        if (!in_array($requestMethod, $this->supportedRequestMethods)) {
            // Only Redirect binding is supported for Single Sign On
            throw new InvalidRequestMethodException(
                sprintf('The HTTP request method "%s" is not supported on this SAML SSO endpoint', $requestMethod)
            );
        }

        if (($requestMethod ===  Request::METHOD_POST && !$request->request->has('SAMLRequest')) ||
            ($requestMethod ===  Request::METHOD_GET && !$request->query->has('SAMLRequest'))) {
            throw new MissingParameterException(
                sprintf('The parameter "SAMLRequest" is missing on this SAML SSO endpoint')
            );
        }

        return true;
    }
}
