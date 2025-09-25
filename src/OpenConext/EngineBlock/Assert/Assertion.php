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

namespace OpenConext\EngineBlock\Assert;

use Assert\Assertion as BaseAssertion;

/**
 * @method static void nullOrNonEmptyString($value, $message = null, $propertyPath = null)
 * @method static void allNonEmptyString($value, $message = null, $propertyPath = null)
 */
class Assertion extends BaseAssertion
{
    const INVALID_NON_EMPTY_STRING  = 1001;
    const INVALID_HASHING_ALGORITHM = 1002;

    protected static $exceptionClass = \OpenConext\EngineBlock\Exception\InvalidArgumentException::class;

    /**
     * @param string $value
     * @param string $propertyPath
     * @return void
     */
    public static function nonEmptyString($value, $propertyPath)
    {
        if (!is_string($value) || trim($value) === '') {
            $message = 'Expected non-empty string for "%s", "%s" given';

            throw static::createException(
                $value,
                sprintf($message, $propertyPath, static::stringify($value)),
                static::INVALID_NON_EMPTY_STRING,
                $propertyPath
            );
        }
    }

    /**
     * @param array       $requiredKeys
     * @param array       $value
     * @param null|string $message
     * @param null|string $propertyPath
     * @return void
     */
    public static function keysExist(array $value, array $requiredKeys, $message = null, $propertyPath = null)
    {
        foreach ($requiredKeys as $requiredKey) {
            self::keyExists($value, $requiredKey, $message, $propertyPath);
        }
    }

    public static function validHashingAlgorithm($hashingAlgorithm)
    {
        Assertion::nonEmptyString($hashingAlgorithm, 'hashingAlgorithm');

        if (!in_array($hashingAlgorithm, hash_algos())) {
            throw static::createException(
                $hashingAlgorithm,
                sprintf('Hashing algorithm "%s" does not exist', $hashingAlgorithm),
                static::INVALID_HASHING_ALGORITHM,
                'hashingAlgorithm'
            );
        }
    }
}
