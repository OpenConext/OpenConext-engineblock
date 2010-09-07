<?php

require_once 'Zend/Ldap.php';

class EngineBlock_UserDirectory
{
    const LDAP_CLASS_COLLAB_PERSON   = 'collabPerson';
    const LDAP_ATTR_COLLAB_PERSON_ID = 'collabPersonId';

    protected $IDENTIFYING_ATTRIBUTES = array(
            'urn:mace:dir:attribute-def:uid',
            'urn:mace:dir:attribute-def:cn' ,
            'urn:mace:dir:attribute-def:sn' ,
            'urn:mace:dir:attribute-def:mail',
            'urn:mace:dir:attribute-def:displayName' ,
            'urn:mace:dir:attribute-def:givenName'
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

        // By default Zend_Ldap will convert the keys to lowercase because LDAP is supposed to be case insensitive
        // but we ARE case sensitive and OpenLDAP returns the proper case anyway.
        $collection->getInnerIterator()->setAttributeNameTreatment(
            Zend_Ldap_Collection_Iterator_Default::ATTRIBUTE_NATIVE
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

    // TODO: cleanup, add constants for strings, document
    public function addUser($organization, $attributes, $attributeHash)
    {
        $now = date(DATE_RFC822);
        $uid = $attributes['uid'][0];
        $info = array(
            'collabPersonId'            => 'urn:collab:person:' . $organization . ':' . $uid,
            'collabPersonHash'          => $attributeHash,
            'collabPersonRegistered'    => $now,
            'collabPersonLastUpdated'   => $now,
            'collabPersonLastAccessed'  => $now,
            // TODO: find out about IDP (need reference)? pass in parameter? call external module?
            'collabPersonIsGuest'       => FALSE,
            '0'                         => $organization,
        );

        foreach ($this->IDENTIFYING_ATTRIBUTES as $identifyingAttribute) {
            if (array_key_exists($identifyingAttribute, $attributes)) {
                $info[$identifyingAttribute] = $attributes[$identifyingAttribute];
            }
        }

        if (! array_key_exists('cn', $info)) {
            $info['cn'] = $this->_getCommonNameFromAttributes($attributes);
        }
        if (! array_key_exists('sn', $info)) {
            $info['sn'] = $info['cn'];
        }
        $info['objectClass'] = array(
            'collabPerson',
            'nlEduPerson',
            'inetOrgPerson',
            'organizationalPerson',
            'person',
            'top',
        );

        $dn = 'uid=' . $uid . ',o=' . $organization . ',' . $this->_getLdapClient()->getBaseDn();

        $this->addOrganization($organization);

        if (!$this->_getLdapClient()->exists($dn)) {
            $result = $this->_getLdapClient()->add($dn, $info);
            $result = ($result instanceof Zend_Ldap);
        } else {
            // TODO: check hash
            $result = TRUE;
        }
        return $result;
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
            return $result = $attributes['sn'][0];
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
                                 'username'             => $config->username,
                                 'password'             => $config->password,
                                 'bindRequiresDn'       => $config->bindRequiresDn,
                                 'accountDomainName'    => $config->accountDomainName,
                                 'baseDn'               => $config->baseDn);
            $this->_ldapClient = new Zend_Ldap($ldapOptions);
            $this->_ldapClient->bind();
        }
        return $this->_ldapClient;
    }
}

/**
 *     public function registerUserForAttributes($attributes, $attributeHash)
    {
        if (! defined('ENGINEBLOCK_USER_DB_DSN') && ENGINEBLOCK_USER_DB_DSN) {
            return false;
        }
        $uid = $attributes[self::USER_ID_ATTRIBUTE][0];
        $dbh = new PDO(ENGINEBLOCK_USER_DB_DSN, ENGINEBLOCK_USER_DB_USER, ENGINEBLOCK_USER_DB_PASSWORD);
        $statement = $dbh->prepare("INSERT INTO `users` (uid, last_seen) VALUES (?, NOW()) ON DUPLICATE KEY UPDATE last_seen = NOW()");
        $statement->execute(array(
            $uid
        ));
        $sqlValues = array();
        $bindValues = array(

            self::USER_ID_ATTRIBUTE => $uid
        );
        $nameCount = 1;
        $valueCount = 1;
        foreach ($attributes as $attributeName => $attributeValues) {
            if ($attributeName === self::USER_ID_ATTRIBUTE) {
                continue;
            }
            $bindValues['attributename' . $nameCount] = $attributeName;
            foreach ($attributeValues as $attributeValue) {
                $sqlValues[] = "(:uid, :attributename{$nameCount}, :attributevalue{$valueCount})";
                $bindValues['attributevalue' . $valueCount] = $attributeValue;
                $valueCount ++;
            }
            $nameCount ++;
        }
        // No other attributes than uid found
        if (empty($sqlValues)) {
            return false;
        }
        $statement = $dbh->prepare("INSERT IGNORE INTO `user_attributes` (`user_uid`, `name`, `value`) VALUES " . implode(',', $sqlValues));
        $statement->execute($bindValues);
    }
 */
