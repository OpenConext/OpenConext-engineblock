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
 * Validate URNs according to RFC-3986.
 *
 * See: http://www.rfc-editor.org/errata_search.php?rfc=3986
 *
 * Note that this is a VERY permissive regex
 */
class EngineBlock_Validator_Urn
{
    /**
     * RFC2141 compliant urn regex
     * based on: http://stackoverflow.com/questions/5492885/is-there-a-java-library-that-validates-urns
     */
    const REGEX = <<<'REGEX'
/^urn:[a-z0-9][a-z0-9-]{1,31}:([a-z0-9()+,-.:=@;$_!*']|%(0[1-9a-f]|[1-9a-f][0-9a-f]))+$/i
REGEX;

    /**
     * @param string $string
     * @return bool
     */
    public function validate($urn)
    {
        return (bool) preg_match(self::REGEX, $urn);
    }
}
