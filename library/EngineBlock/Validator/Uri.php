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

/**
 * Validate URIs according to RFC-3986.
 *
 * See: http://www.rfc-editor.org/errata_search.php?rfc=3986
 *
 * Note that this is a VERY permissive regex
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
        return (bool) preg_match(self::REGEX, $uri);
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
