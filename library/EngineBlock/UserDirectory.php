<?php

use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProviderEntity;

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
    const LDAP_ATTR_COLLAB_PERSON_FIRST_WARNING     = 'collabpersonfirstwarningsent';
    const LDAP_ATTR_COLLAB_PERSON_SECOND_WARNING    = 'collabpersonsecondwarningsent';
    const LDAP_ATTR_EDU_PERSON_EPPN              = 'eduPersonPrincipalName';

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
     * @var Zend_Config
     */
    protected $_ldapConfig = NULL;

    protected $openConextIdentifierType = NULL;

    /**
     * @param Zend_Config $ldapConfig
     */
    public function __construct(Zend_Config $ldapConfig)
    {
        $this->_ldapConfig = $ldapConfig;

        // Supported values: CollabPersonId, CollabPersonUuid and eduPersonPrincipalName
        $this->_openConextIdentifierType = $this->_getOpenConextIdentifierTypeFromConfig();
    }

    /**
     * Find a person by it's (collabPerson)Id
     *
     * @param $identifier
     * @return array[]
     */
    public function findUsersByIdentifier($identifier)
    {
        switch ($this->_openConextIdentifierType) {
            case "CollabPersonId":
                $filter = '(&(objectclass=' . self::LDAP_CLASS_COLLAB_PERSON . ')';
                $filter .= '(' . self::LDAP_ATTR_COLLAB_PERSON_ID . '=' . $identifier . '))';
                break;
            case "CollabPersonUuid":
                $filter = '(&(objectclass=' . self::LDAP_CLASS_COLLAB_PERSON . ')';
                $filter .= '(' . self::LDAP_ATTR_COLLAB_PERSON_UUID . '=' . $identifier . '))';
                break;
            case "eduPersonPrincipalName":
                $filter = '(&(objectclass=' . self::LDAP_CLASS_COLLAB_PERSON . ')';
                $filter .= '(' . self::LDAP_ATTR_EDU_PERSON_EPPN . '=' . $identifier . '))';
                break;
            default:
                $message = 'Whoa, an unknown identifierType was provided: "' .
                    $this->_openConextIdentifierType .
                    '"?!?!?  I only support: CollabPersonId, CollabPersonUuid and ePPN';
                $openConextIdentifierTypeError = new EngineBlock_Exception($message);
                if (!empty($identifier)) {
                    $openConextIdentifierTypeError->userId = $identifier;
                }
                throw $openConextIdentifierTypeError;
        }
        $collection = $this->_getLdapClient()->search(
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

    public function registerUser(array $saml2attributes)
    {
        $ldapAttributes = $this->_getSaml2AttributesFieldMapper()->saml2AttributesToLdapAttributes($saml2attributes);
        $ldapAttributes = $this->_enrichLdapAttributes($ldapAttributes, $saml2attributes);

        $collabPersonId = $this->_getCollabPersonById($ldapAttributes);
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
            if ($e->getCode() === Zend_Ldap_Exception::LDAP_ALREADY_EXISTS) {
                return $this->registerUser($saml2attributes);
            }
            else {
                throw new EngineBlock_Exception("LDAP failure", EngineBlock_Exception::CODE_ALERT, $e);
            }
        }


        return $collabPersonId;
    }

    /**
     * Register that this user has a first warning (for automatic account deprovisioning) sent.
     *
     * @param string $uid
     * @return string collabPersonId
     * @throws EngineBlock_Exception
     */
    public function setUserFirstWarningSent($uid)
    {
        $users = $this->findUsersByIdentifier($uid);

        // Only update a user
        if (count($users) > 1) {
            $e = new EngineBlock_Exception("Multiple users found for UID: '$uid''?!");
            $e->userId = $uid;
            throw $e;
        }

        $newAttributes = array();
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_FIRST_WARNING] = 'TRUE';

        $user = $this->_updateUser($users[0], $newAttributes);

        return $user[self::LDAP_ATTR_COLLAB_PERSON_ID];
    }

    /**
     * Register that this user has a second warning (for automatic account deprovisioning) sent.
     *
     * @throws EngineBlock_Exception
     * @param string $collabPersonId
     * @return string collabPersonId
     */
    public function setUserSecondWarningSent($collabPersonId)
    {
        $users = $this->findUsersByIdentifier($collabPersonId);

        // Only update a user
        if (count($users) > 1) {
            $e = new EngineBlock_Exception("Multiple users found for UID: $collabPersonId?!");
            $e->userId = $collabPersonId;
            throw $e;
        }

        $newAttributes = array();
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_SECOND_WARNING] = 'TRUE';

        $user = $this->_updateUser($users[0], $newAttributes);

        return $user[self::LDAP_ATTR_COLLAB_PERSON_ID];
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
        $this->_getLdapClient()->delete($dn, false);
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
        return 'uid='. $user['uid'] .',o='. $user['o'] .','. $this->_ldapConfig->baseDn;
    }

    protected function _enrichLdapAttributes($ldapAttributes, $saml2attributes)
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
        $ldapAttributes[self::LDAP_ATTR_COLLAB_PERSON_IS_GUEST]      = ($this->_getCollabPersonIsGuest(
            $saml2attributes
        )? 'TRUE' : 'FALSE');
        return $ldapAttributes;
    }

    protected function _addUser($newAttributes)
    {
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_HASH]          = $this->_getCollabPersonHash($newAttributes);

        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_ID]            = $this->_getCollabPersonById($newAttributes);
        $newAttributes[self::LDAP_ATTR_COLLAB_PERSON_UUID]          = $this->_getCollabPersonUuid($newAttributes);

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
        $this->_getLdapClient()->update($dn, $newAttributes);
        
        return $newAttributes;
    }

    protected function _getCollabPersonById($attributes)
    {
        switch ($this->_openConextIdentifierType) {
            case "CollabPersonId":
                $uid = str_replace('@', '_', $attributes['uid']);
                return self::URN_COLLAB_PERSON_NAMESPACE . ':' . $attributes['o'] . ':' . $uid;
                break;
            case "CollabPersonUuid":
                return (string)Surfnet_Zend_Uuid::generate();
                break;
            case "eduPersonPrincipalName":
                return $attributes['eduPersonPrincipalName'];
                break;
            default:
                $message = 'Whoa, an unknown identifierType was provided: "' . $this->_openConextIdentifierType . '"?!?!?  I only support: collabpersonid, collabpersonuuid and edupersonprincipalname';
                $e = new EngineBlock_Exception($message);
                if (!empty($identifier)) {
                    $e->userId = $identifier;
                }
                throw $e;
        }
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
    public function setLdapClient(Zend_Ldap $client)
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

    protected function _getOpenConextIdentifierTypeFromConfig()
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();
        $openConextIdentifierType = $application->getConfigurationValue('openConextIdentifierType', 'CollabPersonId');

        $allowValues = array(
            'CollabPersonId',
            'CollabPersonUuid',
            'eduPersonPrincipalName'
        );
        if (!in_array($openConextIdentifierType, $allowValues)) {
            return 'CollabPersonId';
        }

        return $openConextIdentifierType;
    }
}
