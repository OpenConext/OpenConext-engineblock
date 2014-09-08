<?php

use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProviderEntity;

/**
 *
 */
class EngineBlock_Corto_Filter_Command_AddGuestStatus extends EngineBlock_Corto_Filter_Command_Abstract
{
    const URN_SURF_PERSON_AFFILIATION       = 'urn:oid:1.3.6.1.4.1.1076.20.100.10.10.1';
    const URN_IS_MEMBER_OF                  = 'urn:mace:dir:attribute-def:isMemberOf';

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
        if ($this->_identityProvider->guestQualifier === IdentityProviderEntity::GUEST_QUALIFIER_ALL) {
            // All users from this IdP are guests, so no need to add the isMemberOf
            return;
        }

        if ($this->_identityProvider->guestQualifier === IdentityProviderEntity::GUEST_QUALIFIER_NONE) {
            $this->_setIsMember();
            return;
        }

        $log = EngineBlock_ApplicationSingleton::getLog();
        if ($this->_identityProvider->guestQualifier === IdentityProviderEntity::GUEST_QUALIFIER_SOME) {
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
                $log->attach($this->_identityProvider, 'IDP')
                    ->attach($this->_responseAttributes, 'Attributes')
                    ->warn(
                        "Idp guestQualifier is set to 'Some' however, ".
                        "the surfPersonAffiliation attribute was not provided, " .
                        "not adding the isMemberOf for surf.nl"
                    );
            }
            return;
        }

        // Unknown policy for handling guests? Treat the user as a guest, but issue a warning in the logs
        $log->attach($this->_identityProvider, 'IDP')
            ->attach($this->_responseAttributes, 'Attributes')
            ->warn(
                "Idp guestQualifier is set to unknown value '{$this->_identityProvider['GuestQualifier']}, idp metadata: "
            );
    }

    protected function _setIsMember()
    {
        if (!isset($this->_responseAttributes[static::URN_IS_MEMBER_OF])) {
            $this->_responseAttributes[static::URN_IS_MEMBER_OF] = array();
        }

        $configuration = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
        $this->_responseAttributes[static::URN_IS_MEMBER_OF][] =  $configuration->addgueststatus->guestqualifier;
    }
}