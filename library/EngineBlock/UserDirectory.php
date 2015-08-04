<?php

use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;

/**
 * Access to the LDAP directory where all users are provisioned
 *
 */
class EngineBlock_UserDirectory
{
    const URN_COLLAB_PERSON_NAMESPACE               = 'urn:collab:person';
    const URN_IS_MEMBER_OF                          = 'urn:mace:dir:attribute-def:isMemberOf';

    const LDAP_CLASS_COLLAB_PERSON                  = 'collabPerson';

    const LDAP_ATTR_COLLAB_PERSON_ID                = 'collabpersonid';
    const LDAP_ATTR_COLLAB_PERSON_UUID              = 'collabpersonuuid';
    const LDAP_ATTR_COLLAB_PERSON_HASH              = 'collabpersonhash';
    const LDAP_ATTR_COLLAB_PERSON_REGISTERED        = 'collabpersonregistered';
    const LDAP_ATTR_COLLAB_PERSON_LAST_ACCESSED     = 'collabpersonlastaccessed';
    const LDAP_ATTR_COLLAB_PERSON_LAST_UPDATED      = 'collabpersonlastupdated';
    const LDAP_ATTR_COLLAB_PERSON_IS_GUEST          = 'collabpersonisguest';

    protected $LDAP_OBJECT_CLASSES = array(
        'collabPerson',
        'nlEduPerson',
        'inetOrgPerson',
        'organizationalPerson',
        'person',
        'top',
    );

    /**
     * @var Zend_Ldap
     */
    protected $_ldapClient = NULL;

    /**
     * @param Zend_Ldap $ldapClient
     */
    public function __construct(Zend_Ldap $ldapClient)
    {
        $this->_ldapClient = $ldapClient;
    }

    /**
     * Find a person by it's (collabPerson)Id
     *
     * @param $identifier
     * @return array[]
     */
    public function findUsersByIdentifier($identifier)
    {
        $filter = '(&(objectclass=' . self::LDAP_CLASS_COLLAB_PERSON . ')';
        $filter .= '(' . self::LDAP_ATTR_COLLAB_PERSON_ID . '=' . $identifier . '))';

        $collection = $this->_ldapClient->search(
            $filter,
            null,
            Zend_Ldap::SEARCH_SCOPE_SUB
        );

        // Convert the result from a Zend_Ldap object to a plain multi-dimensional array
        $result = array();
        if (($collection !== NULL) and ($collection !== FALSE)) {
            foreach ($collection as $item) {
                foreach ($item as $key => $value) {
                    if (is_array($value) && count($value) === 1) {
                        $item[$key] = $value[0];
                    }
                }
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * @param array $saml2attributes
     * @param bool $retry
     * @return array
     * @throws EngineBlock_Exception
     * @throws EngineBlock_Exception_MissingRequiredFields
     */
    public function registerUser(array $saml2attributes, $retry = true)
    {
        $ldapAttributes = $this->_getSaml2AttributesFieldMapper()->saml2AttributesToLdapAttributes($saml2attributes);
        $ldapAttributes = $this->_enrichLdapAttributes($ldapAttributes, $saml2attributes);

        $collabPersonId = $this->_getCollabPersonId($ldapAttributes);
        $users = $this->findUsersByIdentifier($collabPersonId);
        try {
            switch (count($users)) {
                case 1:
                    $user = $this->_updateUser($users[0], $ldapAttributes);
                    break;
                case 0:
                    $user = $this->_addUser($ldapAttributes);
                    break;
                default:
                    $message = 'Whoa, multiple users for the same UID: "' . $collabPersonId . '"?!?!?';
                    $e = new EngineBlock_Exception($message);
                    $e->userId = $collabPersonId;
                    throw $e;
            }
        } catch (Zend_Ldap_Exception $e) {
            // Note that during high volumes of logins (like during a performance test) we may see a find
            // not returning a user, then another process registering the user, then the current process failing to
            // add the user because it was already added...
            // So if a user has already been added we simply try again
            if ($retry && $e->getCode() === Zend_Ldap_Exception::LDAP_ALREADY_EXISTS) {
                return $this->registerUser($saml2attributes, false);
            }
            else {
                throw new EngineBlock_Exception("LDAP failure", EngineBlock_Exception::CODE_ALERT, $e);
            }
        }
        return $user;
    }

    /**
     * Delete a user from the LDAP if he/she wants to be removed from the SURFconext platform
     *
     * @param string $collabPersonId
     * @return void
     */
    public function deleteUser($collabPersonId)
    {
        $dn = $this->_buildUserDn($collabPersonId);
        $this->_ldapClient->delete($dn, false);
    }

    /**
     * Build the user dn based on the UID
     *
     * @param string $collabPersonId
     * @return string
     * @throws EngineBlock_Exception
     */
    protected function _buildUserDn($collabPersonId)
    {
        $users = $this->findUsersByIdentifier($collabPersonId);
        if (count($users) !== 1) {
            $e = new EngineBlock_Exception("Multiple or no users found for uid $collabPersonId?");
            $e->userId = $collabPersonId;
            throw $e;
        }
        $user = $users[0];
        return 'uid='. $user['uid'] .',o='. $user['o'] .','. $this->_ldapClient->getBaseDn();
    }

    protected function _enrichLdapAttributes($ldapAttributes, $saml2attributes)
    {
        // cn is required by inetOrgPerson LDAP schema
        if (!isset($ldapAttributes['cn'])) {
            $ldapAttributes['cn'] = $this->_getCommonNameFromAttributes($ldapAttributes);
        }
        if (!isset($ldapAttributes['displayName'])) {
            $ldapAttributes['displayName'] = $ldapAttributes['cn'];
        }
        // sn is required by inetOrgPerson LDAP schema
        if (!isset($ldapAttributes['sn'])) {
            $ldapAttributes['sn'] = $ldapAttributes['cn'];
        }
        // Note that in the default configuration of EngineBlock the following will never trigger
        // because uid and SchacHomeOrganization (which gets mapped to o) are required.
        // @see https://github.com/OpenConext/OpenConext-engineblock/issues/98
        if (!isset($ldapAttributes['uid']) && isset($ldapAttributes['eduPersonPrincipalName'])) {
            list($ldapAttributes['uid']) = explode('@', $ldapAttributes['eduPersonPrincipalName']);
        }
        // @see https://github.com/OpenConext/OpenConext-engineblock/issues/98
        if (!isset($ldapAttributes['o']) && isset($ldapAttributes['eduPersonPrincipalName'])) {
            list(,$ldapAttributes['o']) = explode('@', $ldapAttributes['eduPersonPrincipalName']);
        }
        $ldapAttributes[self::LDAP_ATTR_COLLAB_PERSON_IS_GUEST]      = ($this->_getCollabPersonIsGuest(
            $saml2attributes
        )? 'TRUE' : 'FALSE');
        return $ldapAttributes;
    }

    protected function _addUser($newAttributes)
    {
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_HASH]          = $this->_getCollabPersonHash($newAttributes);

        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_ID]            = $this->_getCollabPersonId($newAttributes);
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_UUID]          = $this->_getCollabPersonUuid($newAttributes);

        $now = date(DATE_RFC822);
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_REGISTERED]    = $now;
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_LAST_ACCESSED] = $now;
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_LAST_UPDATED]  = $now;
        
        $newAttributes['objectClass'] = $this->LDAP_OBJECT_CLASSES;

        $this->_addOrganization($newAttributes['o']);

        $dn = $this->_getDnForLdapAttributes($newAttributes);
        $this->_ldapClient->add($dn, $newAttributes);
        
        return $newAttributes;
    }

    /**
     * Make sure an organization exists in the directory
     *
     * @param  $organization
     * @return bool
     */
    protected function _addOrganization($organization)
    {
        $info = array(
            'o' => $organization ,
            'objectclass' => array(
                'organization' ,
                'top'
            )
        );
        $dn = 'o=' . $organization . ',' . $this->_ldapClient->getBaseDn();
        if (!$this->_ldapClient->exists($dn)) {
            $result = $this->_ldapClient->add($dn, $info);
            $result = ($result instanceof Zend_Ldap);
        } else {
            $result = TRUE;
        }
        return $result;
    }

    protected function _updateUser($user, $newAttributes)
    {
        // Hackish, apparently LDAP gives us arrays even for single values?
        // So for now we assume arrays with only one value are single valued
        foreach ($user as $userKey => $userValue) {
            if (is_array($userValue) && count($userValue) === 1) {
                $user[$userKey] = $userValue[0];
            }
        }

        if ($user[self::LDAP_ATTR_COLLAB_PERSON_HASH] === $this->_getCollabPersonHash($newAttributes)) {
            $now = date(DATE_RFC822);
            $newAttributes = $user + $newAttributes;
            $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_LAST_ACCESSED] = $now;

            return $newAttributes;
        }

        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_HASH] = $this->_getCollabPersonHash($newAttributes);

        $now = date(DATE_RFC822);
        $newAttributes = array_merge($user, $newAttributes);
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_LAST_ACCESSED] = $now;
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_LAST_UPDATED]  = $now;

        $dn = $this->_getDnForLdapAttributes($newAttributes);
        $this->_ldapClient->update($dn, $newAttributes);
        
        return $newAttributes;
    }

    protected function _getCollabPersonId($attributes)
    {
        $uid = str_replace('@', '_', $attributes['uid']);
        return self::URN_COLLAB_PERSON_NAMESPACE . ':' . $attributes['o'] . ':' . $uid;
    }

    protected function _getCollabPersonUuid($attributes)
    {
        return (string)Surfnet_Zend_Uuid::generate();
    }

    protected function _getCollabPersonHash($attributes)
    {
        return md5($this->_getCollabPersonString($attributes));
    }

    protected function _getCollabPersonString($attributes)
    {
        $pairs = array();
        foreach ($attributes as $name => $value) {
            $pairs[] = "$name=$value";
        }
        return implode('&', $pairs);
    }

    /**
     * Figure out of a person with given attributes is a guest user.
     *
     * @param array $saml2attributes
     * @return bool
     */
    protected function _getCollabPersonIsGuest(array $saml2attributes)
    {
        $guestQualifier = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->addgueststatus->guestqualifier;
        return !isset($saml2attributes[self::URN_IS_MEMBER_OF]) || !in_array($guestQualifier, $saml2attributes[self::URN_IS_MEMBER_OF]);
    }

    protected function _getDnForLdapAttributes($attributes)
    {
        return 'uid=' . $attributes['uid'] . ',o=' . $attributes['o'] . ',' . $this->_ldapClient->getBaseDn();
    }

    protected function _getCommonNameFromAttributes($attributes)
    {
        if (isset($attributes['givenName']) && isset($attributes['sn'])) {
            return $attributes['givenName'] . ' ' . $attributes['sn'];
        }

        if (isset($attributes['sn'])) {
            return $attributes['sn'];
        }

        if (isset($attributes['displayName'])) {
            return $attributes['displayName'];
        }

        if (isset($attributes['mail'])) {
            return $attributes['mail'];
        }

        if (isset($attributes['givenName'])) {
            return $attributes['givenName'];
        }

        if (isset($attributes['uid'])) {
            return $attributes['uid'];
        }

        return "";
    }

    protected function _getSaml2AttributesFieldMapper()
    {
        return new EngineBlock_Saml2Attributes_FieldMapper();
    }
}
