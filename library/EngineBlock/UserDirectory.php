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
 * @copyright Copyright ¬© 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

/**
 *
 * @todo This is too tightly coupled to LDAP, we should be able to switch to a simple database
 *
 * @throws EngineBlock_Exception
 *
 */
class EngineBlock_UserDirectory
{
    const URN_COLLAB_PERSON_NAMESPACE               = 'urn:collab:person';
    
    const LDAP_CLASS_COLLAB_PERSON                  = 'collabPerson';

    const LDAP_ATTR_COLLAB_PERSON_ID                = 'collabpersonid';
    const LDAP_ATTR_COLLAB_PERSON_UUID              = 'collabpersonuuid';
    const LDAP_ATTR_COLLAB_PERSON_HASH              = 'collabpersonhash';
    const LDAP_ATTR_COLLAB_PERSON_REGISTERED        = 'collabpersonregistered';
    const LDAP_ATTR_COLLAB_PERSON_LAST_ACCESSED     = 'collabpersonlastaccessed';
    const LDAP_ATTR_COLLAB_PERSON_LAST_UPDATED      = 'collabpersonlastupdated';
    const LDAP_ATTR_COLLAB_PERSON_IS_GUEST          = 'collabpersonisguest';
    const LDAP_ATTR_COLLAB_PERSON_FIRST_WARNING     = 'collabpersonfirstwarningsent';
    const LDAP_ATTR_COLLAB_PERSON_SECOND_WARNING    = 'collabpersonsecondwarningsent';

    protected $LDAP_OBJECT_CLASSES = array(
        'collabPerson',
        'nlEduPerson',
        'inetOrgPerson',
        'organizationalPerson',
        'person',
        'top',
    );

    protected $_ldapClient = NULL;

    protected $_ldapConfig = NULL;

    public function __construct($ldapConfig)
    {
        $this->_ldapConfig = $ldapConfig;
    }

    public function findUsersByIdentifier($identifier, $ldapAttributes = array())
    {
        $filter = '(&(objectclass=' . self::LDAP_CLASS_COLLAB_PERSON . ')';
        $filter .= '(' . self::LDAP_ATTR_COLLAB_PERSON_ID . '=' . $identifier . '))';
        return $this->_fetchResultsForFilter($filter);
    }

    public function findUserByCollabPersonId($collabPersonId)
    {
        $filter = '(&(objectclass=' . self::LDAP_CLASS_COLLAB_PERSON . ')';
        $filter .= '(' . self::LDAP_ATTR_COLLAB_PERSON_ID . '=' . $collabPersonId . '))';
        return $this->_fetchResultsForFilter($filter);
    }

    public function findUserByCollabPersonUuid($collabPersonUuid)
    {
        $filter = '(&(objectclass=' . self::LDAP_CLASS_COLLAB_PERSON . ')';
        $filter .= '(' . self::LDAP_ATTR_COLLAB_PERSON_UUID . '=' . $collabPersonUuid . '))';
        return $this->_fetchResultsForFilter($filter);
    }

    public function fetchAll()
    {
        $filter = '(&(objectclass=' . self::LDAP_CLASS_COLLAB_PERSON . ')';
        return $this->_fetchResultsForFilter($filter);
    }

    protected function _fetchResultsForFilter($filter, $fields = array())
    {
        $collection = $this->_getLdapClient()->search(
            $filter,
            null,
            Zend_Ldap::SEARCH_SCOPE_SUB,
            $fields
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

    /**
     * Set the first warning sent user attribute
     *
     * @param string $uid
     * @return string
     */
    public function setUserFirstWarningSent($uid)
    {
        $users = $this->findUsersByIdentifier($uid);

        // Only update a user
        if (count($users) > 1) {
            throw new EngineBlock_Exception("Multiple users found for UID: $uid?!");
        }

        $newAttributes = array();
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_FIRST_WARNING] = 'TRUE';

        $user = $this->_updateUser($users[0], $newAttributes, array(), array());

        return $user[self::LDAP_ATTR_COLLAB_PERSON_ID];
    }

    /**
     * 
     *
     * @throws EngineBlock_Exception
     * @param string $uid
     * @return string
     */
    public function setUserSecondWarningSent($uid)
    {
        $users = $this->findUsersByIdentifier($uid);

        // Only update a user
        if (count($users) > 1) {
            throw new EngineBlock_Exception("Multiple users found for UID: $uid?!");
        }

        $newAttributes = array();
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_SECOND_WARNING] = 'TRUE';

        $user = $this->_updateUser($users[0], $newAttributes, array(), array());

        return $user[self::LDAP_ATTR_COLLAB_PERSON_ID];
    }

    /**
     * Delete a user from the LDAP if he/she wants to be removed from the SURFconext platform
     *
     * @param  $uid
     * @return void
     */
    public function deleteUser($uid)
    {
        $dn = $this->_buildUserDn($uid);
        $this->_getLdapClient()->delete($dn, false);
    }

    /**
     * Build the user dn based on the UID
     *
     * @param  $uid
     * @return null|string
     */
    protected function _buildUserDn($uid)
    {
        $uidParts = explode(':', $uid);

        if (count($uidParts) >=4) {
            // Only use the third and fourth part, other parts contain person namespace
            return 'uid='. $uidParts[4] .',o='. $uidParts[3] .','. $this->_ldapConfig->baseDn;
        }

        return null;
    }

    protected function _enrichLdapAttributes($ldapAttributes)
    {
        if (!isset($ldapAttributes['cn'])) {
            $ldapAttributes['cn'] = $this->_getCommonNameFromAttributes($ldapAttributes);
        }
        if (!isset($ldapAttributes['displayName'])) {
            $ldapAttributes['displayName'] = $ldapAttributes['cn'];
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

        $this->_addOrganization($newAttributes['o']);

        $dn = $this->_getDnForLdapAttributes($newAttributes);
        $this->_getLdapClient()->add($dn, $newAttributes);
        
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
        $dn = 'o=' . $organization . ',' . $this->_getLdapClient()->getBaseDn();
        if (!$this->_getLdapClient()->exists($dn)) {
            $result = $this->_getLdapClient()->add($dn, $info);
            $result = ($result instanceof Zend_Ldap);
        } else {
            $result = TRUE;
        }
        return $result;
    }

    protected function _updateUser($user, $newAttributes, $saml2attributes, $idpEntityMetadata)
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

            $dn = $this->_getDnForLdapAttributes($newAttributes);
            $this->_getLdapClient()->update($dn, $newAttributes);

            return $newAttributes;
        }

        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_HASH] = $this->_getCollabPersonHash($newAttributes);

        $now = date(DATE_RFC822);
        $newAttributes = array_merge($user, $newAttributes);
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
     * @param array $attributes
     * @param array $saml2attributes
     * @param array $idpEntityMetadata
     * @return bool
     */
    protected function _getCollabPersonIsGuest(array $attributes, array $saml2attributes, array $idpEntityMetadata)
    {
        return ($saml2attributes['urn:oid:1.3.6.1.4.1.1076.20.100.10.10.1'][0]!=='member');
    }

    protected function _getDnForLdapAttributes($attributes)
    {
        return 'uid=' . $attributes['uid'] . ',o=' . $attributes['o'] . ',' . $this->_getLdapClient()->getBaseDn();
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

    /**
     * @param  $client
     * @return EngineBlock_UserDirectory
     */
    public function setLdapClient($client)
    {
        $this->_ldapClient = $client;
        return $this;
    }

    /**
     * @return Zend_Ldap The ldap client
     */
    protected function _getLdapClient()
    {
        if ($this->_ldapClient == NULL) {

            $ldapOptions = array(
                'host'                 => $this->_ldapConfig->host,
                'useSsl'               => $this->_ldapConfig->useSsl,
                'username'             => $this->_ldapConfig->userName,
                'password'             => $this->_ldapConfig->password,
                'bindRequiresDn'       => $this->_ldapConfig->bindRequiresDn,
                'accountDomainName'    => $this->_ldapConfig->accountDomainName,
                'baseDn'               => $this->_ldapConfig->baseDn
            );

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