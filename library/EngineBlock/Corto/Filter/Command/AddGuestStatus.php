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
class EngineBlock_Corto_Filter_Command_AddGuestStatus extends EngineBlock_Corto_Filter_Command_Abstract
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
        // Is a guest user?
        $this->_addIsMemberOfSurfNlAttribute();
    }


    /**
     * Add the 'urn:collab:org:surf.nl' value to the isMemberOf attribute in case a user
     * is considered a 'full member' of the SURFfederation.
     *
     * @return array Response Attributes
     */
    protected function _addIsMemberOfSurfNlAttribute()
    {
        $log = EngineBlock_ApplicationSingleton::getLog();

        if (!isset($this->_idpMetadata['GuestQualifier'])) {
            $log->attach($this->_idpMetadata, 'IDP')
                ->warn('No GuestQualifier for IdP, setting it to "All" and continuing');

            $this->_idpMetadata['GuestQualifier'] = 'All';
        }

        if ($this->_idpMetadata['GuestQualifier'] === 'None') {
            $this->_setIsMember();
        }
        else if ($this->_idpMetadata['GuestQualifier'] === 'Some') {
            if (isset($this->_responseAttributes[static::URN_SURF_PERSON_AFFILIATION][0])) {
                if ($this->_responseAttributes[static::URN_SURF_PERSON_AFFILIATION][0] === 'member') {
                    $this->_setIsMember();
                }
                else {
                    $log->notice(
                        "Idp guestQualifier is set to 'Some', surfPersonAffiliation attribute does not contain " .
                            'the value "member", so not adding isMemberOf for surf.nl'
                    );
                }
            }
            else {
                $log->attach($this->_idpMetadata, 'IDP')
                    ->attach($this->_responseAttributes, 'Attributes')
                    ->warn(
                        "Idp guestQualifier is set to 'Some' however, ".
                        "the surfPersonAffiliation attribute was not provided, " .
                        "not adding the isMemberOf for surf.nl"
                    );
            }
        }
        else if ($this->_idpMetadata['GuestQualifier'] === 'All') {
            // All users from this IdP are guests, so no need to add the isMemberOf
        }
        else {
            // Unknown policy for handling guests? Treat the user as a guest, but issue a warning in the logs
            $log->attach($this->_idpMetadata, 'IDP')
                ->attach($this->_responseAttributes, 'Attributes')
                ->warn(
                    "Idp guestQualifier is set to unknown value '{$this->_idpMetadata['GuestQualifier']}, idp metadata: "
                );
        }
    }

    protected function _setIsMember()
    {
        if (!isset($this->_responseAttributes[static::URN_IS_MEMBER_OF])) {
            $this->_responseAttributes[static::URN_IS_MEMBER_OF] = array();
        }
        $this->_responseAttributes[static::URN_IS_MEMBER_OF][] = self::URN_COLLAB_ORG_SURF;
    }
}