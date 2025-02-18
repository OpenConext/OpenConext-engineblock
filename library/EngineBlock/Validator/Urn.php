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
 * Validate URNs according to RFC-8141.
 *
 * See: https://www.rfc-editor.org/info/rfc8141
 */
class EngineBlock_Validator_Urn
{
    /**
     * RFC8141 compliant urn regex
     * Taken from: https://stackoverflow.com/a/59048720/5494155
     */
    const REGEX = <<<'REGEX'
/\A(?i:urn:(?!urn:)(?<nid>[a-z0-9][a-z0-9-]{1,31}):(?<nss>(?:[-a-z0-9()+,.:=@;$_!*\'&~\/]|%[0-9a-f]{2})+)(?:\?\+(?<rcomponent>.*?))?(?:\?=(?<qcomponent>.*?))?(?:#(?<fcomponent>.*?))?)\z/D
REGEX;

    public function validate(string $urn): bool
    {
        return (bool) preg_match(self::REGEX, $urn);
    }
}
