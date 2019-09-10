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

namespace OpenConext\EngineBlockFunctionalTestingBundle\Validator;

use OpenConext\EngineBlock\Validator\SsoRequestValidator as BaseSsoRequestValidator;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class SsoRequestValidator extends BaseSsoRequestValidator
{
    public function isValid(Request $request)
    {
        // This service is overloaded in order to allow us to throw a custom exception from behat
        if ($request->cookies->has('throwException')) {
            // Remove the cookie to prevent side effects in the next requests
            setcookie("throwException", "", time()-3600);
            throw new RuntimeException($request->cookies->get('throwException'));
        }
        parent::isValid($request);
    }
}
