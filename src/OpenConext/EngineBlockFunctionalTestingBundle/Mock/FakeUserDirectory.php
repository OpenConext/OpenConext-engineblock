<?php

namespace OpenConext\EngineBlockFunctionalTestingBundle\Mock;

use EngineBlock_UserDirectory as UserDirectory;

class FakeUserDirectory extends UserDirectory
{
    private $users = array();

    /**
     * overriding constructor so we can instantiate without arguments
     */
    public function __construct()
    {
    }

    public function findUsersByIdentifier($identifier)
    {
        if (!array_key_exists($identifier, $this->users)) {
            return array();
        }

        return $this->users[$identifier];
    }

    public function registerUser(array $saml2attributes, $retry = true)
    {
        $ldapAttributes = $this->_getSaml2AttributesFieldMapper()->saml2AttributesToLdapAttributes($saml2attributes);
        $ldapAttributes = $this->_enrichLdapAttributes($ldapAttributes, $saml2attributes);

        $ldapAttributes[self::LDAP_ATTR_COLLAB_PERSON_HASH] = $this->_getCollabPersonHash($ldapAttributes);
        $ldapAttributes[self::LDAP_ATTR_COLLAB_PERSON_ID]   = $this->_getCollabPersonId($ldapAttributes);
        $ldapAttributes[self::LDAP_ATTR_COLLAB_PERSON_UUID] = $this->_getCollabPersonUuid($ldapAttributes);

        $now = date(DATE_RFC822);

        $ldapAttributes[self::LDAP_ATTR_COLLAB_PERSON_REGISTERED]    = $now;
        $ldapAttributes[self::LDAP_ATTR_COLLAB_PERSON_LAST_ACCESSED] = $now;
        $ldapAttributes[self::LDAP_ATTR_COLLAB_PERSON_LAST_UPDATED]  = $now;
        $ldapAttributes['objectClass']                               = $this->LDAP_OBJECT_CLASSES;

        $collabPersonId = $this->_getCollabPersonId($ldapAttributes);

        return $this->users[$collabPersonId] = $ldapAttributes;
    }

    public function deleteUser($collabPersonId)
    {
        unset($this->users[$collabPersonId]);
    }
}
