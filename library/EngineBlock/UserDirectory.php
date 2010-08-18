<?php

define('ENGINEBLOCK_LDAP_CLASS_COLLAB_PERSON', 'collabPerson');
define('ENGINEBLOCK_LDAP_ATTR_COLLAB_PERSON_ID', 'collabPersonId');
define('ENGINEBLOCK_EDUPERSON_PREFIX', 'urn:mace:dir:attribute-def:');

require_once 'Zend/Ldap.php';

class EngineBlock_UserDirectory
{
    protected $_ldapClient = NULL;
    const USER_ID_ATTRIBUTE = 'uid';

    public function registerUserForAttributes($attributes, $attributeHash)
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

    /**
     * @return Zend_Ldap The ldap client
     */
    protected function _getLdapClient()
    {
        if ($this->_ldapClient == NULL) {
            $application = EngineBlock_ApplicationSingleton::getInstance();
            $config = $application->getConfiguration();
       
            $ldapOptions = array('host' => $config['ldap.host'], 
                                 'useSsl' => $config['ldap.useSsl'], 
                                 'username' => $config['ldap.username'],
                                 'password' => $config['ldap.password'],
                                 'bindRequiresDn' => $config['ldap.bindRequiresDn'],
                                 'accountDomainName' => $config['ldap.accountDomainName'],
                                 'baseDn' => $config['ldap.baseDn']);
            $this->_ldapClient = new Zend_Ldap($ldapOptions);
            $this->_ldapClient->bind();
        }
        return $this->_ldapClient;
    }

    public function setLdapClient($client)
    {
        $this->_ldapClient = $client;
    }



    public function findUsersByIdentifier($identifier, $ldapAttributes = array())
    {
        $filter = '(&(objectclass=' . ENGINEBLOCK_LDAP_CLASS_COLLAB_PERSON . ')';
        $filter .= '(' . ENGINEBLOCK_LDAP_ATTR_COLLAB_PERSON_ID . '=' . $identifier . '))';
        $collection = $this->_getLdapClient()->search($filter, null, Zend_Ldap::SEARCH_SCOPE_SUB, $ldapAttributes);
        $result = array();
        if (($collection !== NULL) and ($collection !== FALSE)) {
            foreach ($collection as $item) {
                $result[] = $item;
            }
        }
        return $result;
    }

    private function _getCommonNameFromAttributes($attrs)
    {
        $result = NULL;
        if ((array_key_exists('givenName', $attrs)) and (array_key_exists('sn', $attrs))) {
            $result = $attrs['givenName'][0] . ' ' . $attrs['sn'][0];
        } else 
            if (array_key_exists('sn', $attrs)) {
                $result = $attrs['sn'][0];
            } else 
                if (array_key_exists('displayName', $attrs)) {
                    $result = $attrs['displayName'][0];
                } else 
                    if (array_key_exists('mail', $attrs)) {
                        $result = $attrs['mail'][0];
                    } else 
                        if (array_key_exists('givenName', $attrs)) {
                            $result = $attrs['givenName'][0];
                        } else {
                            $result = $attrs['uid'][0];
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
        if (! $this->_getLdapClient()->exists($dn)) {
            $result = $this->_getLdapClient()->add($dn, $info);
            $result = ($result instanceof Zend_Ldap);
        } else {
            $result = TRUE;
        }
        return $result;
    }

    // TODO: cleanup, add constants for strings, document
    public function addUser($organization, $attributes, $attributeHash)
    {
        $now = date(DATE_RFC822);
        $info = array();
        $info['collabPersonHash'] = $attributeHash;
        $info['collabPersonRegistered'] = $now;
        $info['collabPersonLastUpdated'] = $now;
        $info['collabPersonLastAccessed'] = $now;
        // TODO: find out about IDP (need reference)? pass in parameter? call external module?
        $info['collabPersonIsGuest'] = FALSE;
        foreach (array(
            'uid' , 'cn' , 
            'sn' , 'mail' , 
            'displayName' , 
            'givenName'
        ) as $attribute) {
            if (array_key_exists(ENGINEBLOCK_EDUPERSON_PREFIX . $attribute, $attributes)) {
                $info[$attribute] = $attributes[ENGINEBLOCK_EDUPERSON_PREFIX . $attribute];
            }
        }
        // check mandatory attributes (uid)
        if (! array_key_exists('cn', $info))
            $info['cn'] = $this->_getCommonNameFromAttributes($info);
        if (! array_key_exists('sn', $info))
            $info['sn'] = $info['cn'];
        $info['objectClass'] = array(
            'collabPerson' , 
            'nlEduPerson' , 
            'inetOrgPerson' , 
            'organizationalPerson' , 
            'person' , 'top'
        );
        $info['o'] = $organization;
        $info['collabPersonId'] = 'urn:collab:person:' . $organization . ':' . $info['uid'][0];
        $dn = 'uid=' . $info['uid'][0] . ',o=' . $organization . ',' . $this->_getLdapClient()->getBaseDn();
        $this->addOrganization($organization);
        if (! $this->_getLdapClient()->exists($dn)) {
            $result = $this->_getLdapClient()->add($dn, $info);
            $result = ($result instanceof Zend_Ldap);
        } else {
            // TODO: check hash
            $result = TRUE;
        }
        return $result;
    }
}
