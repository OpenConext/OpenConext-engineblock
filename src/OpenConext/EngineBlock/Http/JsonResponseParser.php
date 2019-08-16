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

namespace OpenConext\EngineBlock\Http;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\InvalidJsonException;

final class JsonResponseParser
{
    /**
     * Function to provide functionality common to Guzzle 5 Response's json method,
     * without config options as they are not needed.
     *
     * @param string $json
     * @return mixed
     * @throws InvalidJsonException
     */
    public static function parse($json)
    {
        Assertion::string($json, 'JSON data "%s" expected to be string, type %s given');

        static $jsonErrors = [
            JSON_ERROR_DEPTH          => 'JSON_ERROR_DEPTH - Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'JSON_ERROR_STATE_MISMATCH - Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR      => 'JSON_ERROR_CTRL_CHAR - Unexpected control character found',
            JSON_ERROR_SYNTAX         => 'JSON_ERROR_SYNTAX - Syntax error, malformed JSON',
            JSON_ERROR_UTF8           => 'JSON_ERROR_UTF8 - Malformed UTF-8 characters, possibly incorrectly encoded',
        ];

        $data = json_decode($json, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            $last         = json_last_error();
            $errorMessage = $jsonErrors[$last];

            if (!isset($errorMessage)) {
                $errorMessage = 'Unknown error';
            }

            throw new InvalidJsonException((sprintf('Unable to parse JSON data: "%s"', $errorMessage)));
        }

        return $data;
    }
}
