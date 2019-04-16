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

use Exception;
use OpenConext\EngineBlock\Exception\InvalidBindingException;
use SAML2\Binding;
use SAML2\HTTPPost;
use SAML2\HTTPRedirect;
use Symfony\Component\HttpFoundation\Request;

/**
 * The SamlBindingValidator verifies valid saml binding
 *
 * Valid requests method / binding combinations for SSO are:
 *
 *  | SAML Binding     | Request method | Parameter name              |
 *  | ---------------- | -------------- | --------------------------- |
 *  | HttpRedirect     | GET            | SAMLRequest,SAMLResponse    |
 *  | HTTPPost         | POST           | SAMLRequest,SAMLResponse    |
 */
class SamlBindingValidator implements RequestValidator
{
    public function isValid(Request $request)
    {
        try {
            $binding = Binding::getCurrentBinding();
        } catch (Exception $e) {
            throw new InvalidBindingException(
                sprintf('No SAMLRequest or SAMLResponse parameter was found in the HTTP "%s" request parameters', $request->getMethod()),
                0,
                $e
            );
        }
        if (!($binding instanceof HTTPRedirect || $binding instanceof HTTPPost)) {
            // We only support HTTP Redirect binding
            throw new InvalidBindingException(
                sprintf('The binding type "%s" is not supported on this endpoint', get_class($binding))
            );
        }
        return true;
    }
}
