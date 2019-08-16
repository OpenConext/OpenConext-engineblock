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

namespace OpenConext\EngineBlockBundle\Exception;

use InvalidArgumentException as CoreInvalidArgumentException;

class InvalidArgumentException extends CoreInvalidArgumentException
{
    public static function invalidType($expectedType, $propertyPath, $parameter)
    {
        return new self(
            sprintf(
                'Invalid argument "%s": "%s" expected, "%s" given',
                $propertyPath,
                $expectedType,
                is_object($parameter) ? get_class($parameter) : gettype($parameter)
            )
        );
    }
}
