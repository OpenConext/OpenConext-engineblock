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

namespace OpenConext\EngineBlock\Exception;

use Assert\InvalidArgumentException as InvalidAssertionException;

class InvalidArgumentException extends InvalidAssertionException implements Exception
{
    public function __construct($message, $code, $propertyPath = null, $value = null, array $constraints = [])
    {
        if ($propertyPath !== null && strpos($message, $propertyPath) === false) {
            $message = sprintf('Invalid argument given for "%s": %s', $propertyPath, $message);
        }

        parent::__construct($message, $code, $propertyPath, $value, $constraints);
    }
}
