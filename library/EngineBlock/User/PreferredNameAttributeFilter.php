<?php
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
