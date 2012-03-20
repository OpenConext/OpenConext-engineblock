<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

/**
 *
 */
class EngineBlock_Corto_Filter_Command_AddMissingAttributes extends EngineBlock_Corto_Filter_Command_Abstract
{
    const URN_SURF_PERSON_AFFILIATION       = 'urn:oid:1.3.6.1.4.1.1076.20.100.10.10.1';
    const URN_IS_MEMBER_OF                  = 'urn:mace:dir:attribute-def:isMemberOf';
    const URN_COLLAB_ORG_SURF               = 'urn:collab:org:surf.nl';

    /**
     * This command may modify the response attributes
     *
     * @return array
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function execute()
    {
        // If we don't have a commonName, determine one from the attributes
        if (!isset($this->_responseAttributes['urn:mace:dir:attribute-def:cn'][0])) {
            $this->_responseAttributes['urn:mace:dir:attribute-def:cn'] = array(
                $this->_determineCommonNameFromAttributes($this->_responseAttributes)
            );
        }
        // If we don't have a displayName, use the commonName
        if (!isset($this->_responseAttributes['urn:mace:dir:attribute-def:displayName'][0])) {
            $this->_responseAttributes['urn:mace:dir:attribute-def:displayName'] = array(
                $this->_responseAttributes['urn:mace:dir:attribute-def:cn'][0]
            );
        }
        // If we don't have a surName use the commonName
        if (!isset($this->_responseAttributes['urn:mace:dir:attribute-def:sn'])) {
            $this->_responseAttributes['urn:mace:dir:attribute-def:sn'] = array(
                $this->_responseAttributes['urn:mace:dir:attribute-def:cn'][0]
            );
        }
        // Is a guest user?
        $this->_addIsMemberOfSurfNlAttribute();
    }

    protected function _determineCommonNameFromAttributes()
    {
        if (isset($this->_responseAttributes['urn:mace:dir:attribute-def:givenName'][0]) &&
            isset($this->_responseAttributes['urn:mace:dir:attribute-def:sn'][0])) {
            return $this->_responseAttributes['urn:mace:dir:attribute-def:givenName'][0] . ' ' .
                $this->_responseAttributes['urn:mace:dir:attribute-def:sn'][0];
        }

        if (isset($this->_responseAttributes['urn:mace:dir:attribute-def:sn'][0])) {
            return $this->_responseAttributes['urn:mace:dir:attribute-def:sn'][0];
        }

        if (isset($this->_responseAttributes['urn:mace:dir:attribute-def:displayName'][0])) {
            return $this->_responseAttributes['urn:mace:dir:attribute-def:displayName'][0];
        }

        if (isset($this->_responseAttributes['urn:mace:dir:attribute-def:mail'][0])) {
            return $this->_responseAttributes['urn:mace:dir:attribute-def:mail'][0];
        }

        if (isset($this->_responseAttributes['urn:mace:dir:attribute-def:givenName'][0])) {
            return $this->_responseAttributes['urn:mace:dir:attribute-def:givenName'][0];
        }

        return $this->_responseAttributes['urn:mace:dir:attribute-def:uid'][0];
    }

    /**
     * Add the 'urn:collab:org:surf.nl' value to the isMemberOf attribute in case a user
     * is considered a 'full member' of the SURFfederation.
     *
     * @param array $this->_responseAttributes
     * @param array $this->_idpMetadata
     * @return array Resonse Attributes
     */
    protected function _addIsMemberOfSurfNlAttribute()
    {
        // Determine guest status
        if (!isset($this->_idpMetadata['GuestQualifier'])) {
            EngineBlock_ApplicationSingleton::getLog()->warn(
                'No GuestQualifier for IdP: ' . var_export($this->_idpMetadata, true) .
                    'Setting it to "All" and continuing.'
            );
            $this->_idpMetadata['GuestQualifier'] = 'All';
        }

        if ($this->_idpMetadata['GuestQualifier'] === 'None') {
            if (!isset($this->_responseAttributes[static::URN_IS_MEMBER_OF])) {
                $this->_responseAttributes[static::URN_IS_MEMBER_OF] = array();
            }
            $this->_responseAttributes[static::URN_IS_MEMBER_OF][] = self::URN_COLLAB_ORG_SURF;
        }
        else if ($this->_idpMetadata['GuestQualifier'] === 'Some') {
            if (isset($this->_responseAttributes[static::URN_SURF_PERSON_AFFILIATION][0])) {
                if ($this->_responseAttributes[static::URN_SURF_PERSON_AFFILIATION][0] === 'member') {
                    if (!isset($this->_responseAttributes[static::URN_IS_MEMBER_OF])) {
                        $this->_responseAttributes[static::URN_IS_MEMBER_OF] = array();
                    }
                    $this->_responseAttributes[static::URN_IS_MEMBER_OF][] = self::URN_COLLAB_ORG_SURF;
                }
                else {
                    EngineBlock_ApplicationSingleton::getLog()->notice(
                        "Idp guestQualifier is set to 'Some', surfPersonAffiliation attribute does not contain " .
                            'the value "member", so not adding isMemberOf for surf.nl'
                    );
                }
            }
            else {
                EngineBlock_ApplicationSingleton::getLog()->warn(
                    "Idp guestQualifier is set to 'Some' however, ".
                        "the surfPersonAffiliation attribute was not provided, " .
                        "not adding the isMemberOf for surf.nl" .
                        var_export($this->_idpMetadata, true) .
                        var_export($this->_responseAttributes, true)
                );
            }
        }
        return $this->_responseAttributes;
    }
}