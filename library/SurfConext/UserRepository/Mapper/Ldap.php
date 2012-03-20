<?php

/**
 * A DataMapper "moves data between objects and a database while keeping them
 * independent of each other and the mapper itself" (Fowler, PoEAA, Mapper)
 */
class SurfConext_UserRepository_Mapper_Ldap
{
    const LDAP_CLASS_COLLAB_PERSON = 'collabPerson';

    const LDAP_ATTR_COLLAB_PERSON_ID   = 'collabpersonid';
    const LDAP_ATTR_COLLAB_PERSON_UUID = 'collabpersonuuid';

    protected $_dao;

    public function __construct(SurfConext_UserRepository_Dao_Ldap $ldap)
    {
        $this->_dao = $ldap;
    }

    public function findByCollabPersonId($collabPersonId)
    {
        $filter = '(&(objectclass=' . self::LDAP_CLASS_COLLAB_PERSON . ')';
        $filter .= '(' . self::LDAP_ATTR_COLLAB_PERSON_ID . '=' . $collabPersonId . '))';
        
        return $this->_findByFilter($filter);
    }

    public function findByCollabPersonUuid($collabPersonUuid)
    {
        $filter = '(&(objectclass=' . self::LDAP_CLASS_COLLAB_PERSON . ')';
        $filter .= '(' . self::LDAP_ATTR_COLLAB_PERSON_UUID . '=' . $collabPersonUuid . '))';

        return $this->_findByFilter($filter);
    }

    protected function _findByFilter($filter)
    {
        $results = $this->_dao->find($filter);

        $users = array();
        foreach ($results as $result) {
            $users[] = $this->_mapLdapResultToUser($result);
        }
        return $users;
    }

    public function count()
    {
        return $this->_dao->count('(&(objectclass=' . self::LDAP_CLASS_COLLAB_PERSON . ')');
    }

    public function insert(SurfConext_User $user)
    {
        $this->_addOrganization($user->o);

        $data = $this->_mapUserToLdapData($user);
        $dn = $this->_buildUserDn($data['uid'], $data['o']);
        return $this->_dao->insert($dn, $data);
    }

    public function update(SurfConext_User $user)
    {
        $this->_addOrganization($user->o);

        $data = $this->_mapUserToLdapData($user);
        $dn = $this->_buildUserDn($user->uid, $user->o);
        return $this->_dao->update($dn, $data);
    }

    public function delete(SurfConext_User $user)
    {
        $dn = $this->_buildUserDn($user->uid, $user->o);
        return $this->_dao->delete($dn);
    }
    
    /**
     * Build the user dn based on the UID
     *
     * @param string $uid
     * @param string $o
     * @return null|string
     */
    protected function _buildUserDn($uid, $o)
    {
        // Only use the third and fourth part, other parts contain person namespace
        return 'uid=' . $uid . ',o=' . $o . ',' . $this->_dao->getBaseDn();
    }

    /**
     * Make sure an organization exists in the directory
     *
     * @param  $organization
     * @return bool
     */
    protected function _addOrganization($organization)
    {
        $data = array(
            'o' => $organization ,
            'objectclass' => array(
                'organization' ,
                'top'
            )
        );
        $dn = 'o=' . $organization . ',' . $this->_dao->getBaseDn();
        if (!$this->_dao->exists($dn)) {
            $result = $this->_dao->insert($dn, $data);
            return ($result instanceof Zend_Ldap);
        } else {
            return TRUE;
        }
    }

    protected function _mapLdapResultToUser($data)
    {
        $user = new SurfConext_User();

        $properties = array_keys(get_object_vars($user));
        foreach ($properties as $property) {
            $ldapKey = strtolower($property);
            if (isset($data[$ldapKey])) {
                if (is_array($data[$ldapKey]) && count($data[$ldapKey]) === 1) {
                    $user->$property = $data[$ldapKey][0];
                }
                else {
                    $user->$property = $data[$ldapKey];
                }
            }
        }
        return $user;
    }

    protected function _mapUserToLdapData(SurfConext_User $user)
    {
        return $user->toArray();
    }
}