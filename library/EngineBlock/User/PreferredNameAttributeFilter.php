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

class EngineBlock_User_PreferredNameAttributeFilter
{
    /**
     * Determines the correct attribute to use for the name of a sender for example when emailing this person
     *
     * @param array $attributes
     * @return string
     */
    public function getAttribute(array $attributes)
    {
        if (isset($attributes['urn:mace:dir:attribute-def:givenName']) && isset($attributes['urn:mace:dir:attribute-def:sn'])) {
            return $attributes['urn:mace:dir:attribute-def:givenName'][0] . ' ' . $attributes['urn:mace:dir:attribute-def:sn'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:cn'])) {
            return $attributes['urn:mace:dir:attribute-def:cn'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:displayName'])) {
            return $attributes['urn:mace:dir:attribute-def:displayName'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:givenName'])) {
            return $attributes['urn:mace:dir:attribute-def:givenName'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:sn'])) {
            return $attributes['urn:mace:dir:attribute-def:sn'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:mail'])) {
            return $attributes['urn:mace:dir:attribute-def:mail'][0];
        }

        if (isset($attributes['urn:mace:dir:attribute-def:uid'])) {
            return $attributes['urn:mace:dir:attribute-def:uid'][0];
        }

        return "";
    }
}
