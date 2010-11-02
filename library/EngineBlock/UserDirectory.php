<?php

class EngineBlock_UserDirectory
{
    const URN_COLLAB_PERSON_NAMESPACE           = 'urn:collab:person';
    const LDAP_CLASS_COLLAB_PERSON              = 'collabPerson';
    const LDAP_ATTR_COLLAB_PERSON_ID            = 'collabpersonid';
    const LDAP_ATTR_COLLAB_PERSON_HASH          = 'collabpersonhash';
    const LDAP_ATTR_COLLAB_PERSON_REGISTERED    = 'collabpersonregistered';
    const LDAP_ATTR_COLLAB_PERSON_LAST_ACCESSED = 'collabpersonlastAccessed';
    const LDAP_ATTR_COLLAB_PERSON_LAST_UPDATED  = 'collabpersonlastupdated';
    const LDAP_ATTR_COLLAB_PERSON_IS_GUEST      = 'collabpersonisguest';

    protected $LDAP_OBJECT_CLASSES = array(
        'collabPerson',
        'nlEduPerson',
        'inetOrgPerson',
        'organizationalPerson',
        'person',
        'top',
    );

    protected $_ldapClient = NULL;

    public function findUsersByIdentifier($identifier, $ldapAttributes = array())
    {
        $filter = '(&(objectclass=' . self::LDAP_CLASS_COLLAB_PERSON . ')';
        $filter .= '(' . self::LDAP_ATTR_COLLAB_PERSON_ID . '=' . $identifier . '))';

        $collection = $this->_getLdapClient()->search(
            $filter,
            null,
            Zend_Ldap::SEARCH_SCOPE_SUB,
            $ldapAttributes
        );

        // Convert the result fron a Zend_Ldap object to a plain multi-dimensional array
        $result = array();
        if (($collection !== NULL) and ($collection !== FALSE)) {
            foreach ($collection as $item) {
                $result[] = $item;
            }
        }
        return $result;
    }

    public function registerUser(array $saml2attributes, array $idpEntityMetadata)
    {
        $ldapAttributes = $this->_getSaml2AttributesFieldMapper()->saml2AttributesToLdapAttributes($saml2attributes);
        $ldapAttributes = $this->_enrichLdapAttributes($ldapAttributes);

        $uid = $this->_getCollabPersonId($ldapAttributes);
        $users = $this->findUsersByIdentifier($uid);
        switch (count($users)) {
            case 1:
                $user = $this->_updateUser($users[0], $ldapAttributes, $saml2attributes, $idpEntityMetadata);
                break;
            case 0:
                $user = $this->_addUser($ldapAttributes, $saml2attributes, $idpEntityMetadata);
                break;
            default:
                $message = 'Whoa, multiple users for the same UID: "' . $uid . '"?!?!?';
                throw new EngineBlock_Exception($message);
        }
        return $user[self::LDAP_ATTR_COLLAB_PERSON_ID];
    }

    protected function _enrichLdapAttributes($ldapAttributes)
    {
        if (!isset($ldapAttributes['cn'])) {
            $ldapAttributes['cn'] = $this->_getCommonNameFromAttributes($ldapAttributes);
        }
        if (!isset($ldapAttributes['sn'])) {
            $ldapAttributes['sn'] = $ldapAttributes['cn'];
        }
        return $ldapAttributes;
    }

    protected function _addUser($newAttributes, $saml2attributes, $idpEntityMetadata)
    {
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_HASH]          = $this->_getCollabPersonHash($newAttributes);

        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_ID]            = $this->_getCollabPersonId($newAttributes);
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_IS_GUEST]      = ($this->_getCollabPersonIsGuest(
            $newAttributes, $saml2attributes, $idpEntityMetadata
        )? 'TRUE' : 'FALSE');

        $now = date(DATE_RFC822);
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_REGISTERED]    = $now;
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_LAST_ACCESSED] = $now;
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_LAST_UPDATED]  = $now;
        
        $newAttributes['objectClass'] = $this->LDAP_OBJECT_CLASSES;

        $this->addOrganization($newAttributes['o']);

        $dn = $this->_getDnForLdapAttributes($newAttributes);
        $this->_getLdapClient()->add($dn, $newAttributes);
        
        return $newAttributes;
    }

    protected function _updateUser($user, $newAttributes, $saml2attributes, $idpEntityMetadata)
    {
        // Hackish, appearantly LDAP gives us arrays even for single values?
        // So for now we assume arrays with only one value are single valued
        foreach ($user as $userKey => $userValue) {
            if (is_array($userValue) && count($userValue) === 1) {
                $user[$userKey] = $userValue[0];
            }
        }
        
        if ($user[self::LDAP_ATTR_COLLAB_PERSON_HASH]===$this->_getCollabPersonHash($newAttributes)) {
            $now = date(DATE_RFC822);
            $newAttributes = $user + $newAttributes;
            $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_LAST_ACCESSED] = $now;

            $dn = $this->_getDnForLdapAttributes($newAttributes);
            $this->_getLdapClient()->update($dn, $newAttributes);

            return $newAttributes;
        }

        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_HASH] = $this->_getCollabPersonHash($newAttributes);

        $now = date(DATE_RFC822);
        $newAttributes = $user + $newAttributes;
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_LAST_ACCESSED] = $now;
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_LAST_UPDATED]  = $now;
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_IS_GUEST]      = ($this->_getCollabPersonIsGuest(
            $newAttributes, $saml2attributes, $idpEntityMetadata
        )? 'TRUE' : 'FALSE');

        $dn = $this->_getDnForLdapAttributes($newAttributes);
        $this->_getLdapClient()->update($dn, $newAttributes);
        
        return $newAttributes;
    }

    protected function _getCollabPersonId($attributes)
    {
        $uid = str_replace('@', '_', $attributes['uid']);
        return self::URN_COLLAB_PERSON_NAMESPACE . ':' . $attributes['o'] . ':' . $uid;
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
     * Algorithm is as follows:
     * - By default you are a guest.
     * - Unless you have a 'urn:surfnet:entl:intesllingsgebruik' attribute with the value 'instellingsgebruiker'
     * - Or, if you do not have this attribute, if your Idp is marked as part of the 'federation' in the EngineBlock configuration
     *
     * @param  $attributes
     * @param  $saml2attributes
     * @param  $idpEntityMetadata
     * @return bool
     */
    protected function _getCollabPersonIsGuest($attributes, $saml2attributes, $idpEntityMetadata)
    {
        $isGuest = true;

        $configuration = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
        if (isset($saml2attributes['urn:surfnet:entl:instellingsgebruik']) &&
            $saml2attributes['urn:surfnet:entl:instellingsgebruik']==='instellingsgebruiker') {
            $isGuest = false;
        }
        /**
         * @todo This is a hack for now, this SHOULD be set in the Service Registry with a custom attribute
         */
        else if (isset($configuration->federationIdps)) {
            $isGuest = !in_array($idpEntityMetadata['EntityId'], $configuration->federationIdps->toArray());
        }
        return $isGuest;
    }

    protected function _getDnForLdapAttributes($attributes)
    {
        return 'uid=' . $attributes['uid'] . ',o=' . $attributes['o'] . ',' . $this->_getLdapClient()->getBaseDn();
    }

    public function addOrganization($organization)
    {
        $info = array(
            'o' => $organization ,
            'objectclass' => array(
                'organization' ,
                'top'
            )
        );
        $dn = 'o=' . $organization . ',' . $this->_getLdapClient()->getBaseDn();
        if (!$this->_getLdapClient()->exists($dn)) {
            $result = $this->_getLdapClient()->add($dn, $info);
            $result = ($result instanceof Zend_Ldap);
        } else {
            $result = TRUE;
        }
        return $result;
    }


    protected function _getCommonNameFromAttributes($attributes)
    {
        if (isset($attributes['givenName'][0]) && isset($attributes['sn'][0])) {
            return $attributes['givenName'][0] . ' ' . $attributes['sn'][0];
        }

        if (isset($attributes['sn'][0])) {
            return $attributes['sn'][0];
        }

        if (isset($attributes['displayName'][0])) {
            return $attributes['displayName'][0];
        }

        if (isset($attributes['mail'][0])) {
            return $attributes['mail'][0];
        }

        if (isset($attributes['givenName'][0])) {
            return $attributes['givenName'][0];
        }

        if (isset($attributes['uid'][0])) {
            return $attributes['uid'][0];
        }

        return "";
    }

    public function setLdapClient($client)
    {
        $this->_ldapClient = $client;
    }

    /**
     * @return Zend_Ldap The ldap client
     */
    protected function _getLdapClient()
    {
        if ($this->_ldapClient == NULL) {
            $application = EngineBlock_ApplicationSingleton::getInstance();
            $config = $application->getConfiguration()->ldap;

            $ldapOptions = array('host'                 => $config->host,
                                 'useSsl'               => $config->useSsl,
                                 'username'             => $config->userName,
                                 'password'             => $config->password,
                                 'bindRequiresDn'       => $config->bindRequiresDn,
                                 'accountDomainName'    => $config->accountDomainName,
                                 'baseDn'               => $config->baseDn);

            $this->_ldapClient = new Zend_Ldap($ldapOptions);
            $this->_ldapClient->bind();
        }
        return $this->_ldapClient;
    }

    protected function _getSaml2AttributesFieldMapper()
    {
        return new EngineBlock_Saml2Attributes_FieldMapper();
    }
}
