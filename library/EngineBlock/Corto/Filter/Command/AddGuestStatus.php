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

use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;

/**
 *
 */
class EngineBlock_Corto_Filter_Command_AddGuestStatus extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface
{
    const URN_SURF_PERSON_AFFILIATION       = 'urn:oid:1.3.6.1.4.1.1076.20.100.10.10.1';
    const URN_IS_MEMBER_OF                  = 'urn:mace:dir:attribute-def:isMemberOf';

    /**
     * {@inheritdoc}
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
        if ($this->_identityProvider->getCoins()->guestQualifier() === IdentityProvider::GUEST_QUALIFIER_ALL) {
            // All users from this IdP are guests, so no need to add the isMemberOf
            return;
        }

        if ($this->_identityProvider->getCoins()->guestQualifier() === IdentityProvider::GUEST_QUALIFIER_NONE) {
            $this->_setIsMember();
            return;
        }

        $log = EngineBlock_ApplicationSingleton::getLog();
        if ($this->_identityProvider->getCoins()->guestQualifier() === IdentityProvider::GUEST_QUALIFIER_SOME) {
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
                $log->warning(
                    "Idp guestQualifier is set to 'Some' however, ".
                    "the surfPersonAffiliation attribute was not provided, " .
                    "not adding the isMemberOf for surf.nl",
                    array(
                        'idp' => $this->_identityProvider,
                        'response_attributes' => $this->_responseAttributes,
                    )
                );
            }
            return;
        }

        // Unknown policy for handling guests? Treat the user as a guest, but issue a warning in the logs
        $log->warning(
            "Idp guestQualifier is set to unknown value '{$this->_identityProvider['GuestQualifier']}",
            array(
                'idp' => $this->_identityProvider,
                'response_attributes' => $this->_responseAttributes,
            )
        );
    }

    protected function _setIsMember()
    {
        if (!isset($this->_responseAttributes[static::URN_IS_MEMBER_OF])) {
            $this->_responseAttributes[static::URN_IS_MEMBER_OF] = array();
        }

        $qualifier = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()
            ->getGuestStatusQualifier();
        $this->_responseAttributes[static::URN_IS_MEMBER_OF][] = $qualifier;
    }
}
