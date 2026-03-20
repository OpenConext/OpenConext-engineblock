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

use SimpleSAML\Assert\Assert;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Validate URIs using simplesamlphp/assert.
 *
 * The legacy regex is kept for the unused parse() helper so existing debug
 * output shape remains available if it is ever reintroduced.
 */
class EngineBlock_Validator_Uri
{
    const REGEX = '/^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?/';

    /**
     * @param string $string
     * @return bool
     */
    public function validate($uri)
    {
        try {
            Assert::validURI($uri);
        } catch (AssertionFailedException $e) {
            return false;
        }

        return true;
    }

    /**
     * Parses the given uri with the regex, this is useful for debugging
     *
     * @param string $uri
     * @return array
     */
    public static function parse($uri)
    {
        preg_match(self::REGEX, $uri, $matches);

        $keys = ['match'];
        $keys[] = 'scheme+separator';
        $keys[] = 'scheme';
        $keys[] = 'host+separator';
        $keys[] = 'host';
        $keys[] = 'path';
        $keys[] = 'query+separator';
        $keys[] = 'query';
        $keys[] = 'anchor+separator';
        $keys[] = 'anchor';

        $keysMatched = array_slice($keys, 0, count($matches));
        return array_combine($keysMatched, $matches);
    }
}
