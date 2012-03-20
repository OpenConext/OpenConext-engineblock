<?php

/**
 * A DAO "separates a data resource's client interface from its data access mechanisms
 * / adapts a specific data resource's access API to a generic client interface"
 * allowing "data access mechanisms to change independently of the code that uses the data"
 * (Sun Blueprints)
 */
class SurfConext_UserRepository_Dao_Ldap
{
    protected $_ldapClient;
    protected $_ldapConfig;

    /**
     * @param $ldapConfig
     */
    public function __construct($ldapConfig)
    {
        $this->_ldapConfig = $ldapConfig;
    }

    public function find($filter)
    {
        $collection = $this->_getLdapClient()->search(
            $filter,
            null,
            Zend_Ldap::SEARCH_SCOPE_SUB
        );

        // Convert the result from a Zend_Ldap object to a plain multi-dimensional array
        $result = array();
        if (($collection !== NULL) and ($collection !== FALSE)) {
            foreach ($collection as $item) {
                $result[] = $item;
            }
        }
        return $result;
    }

    public function insert($dn, $data)
    {
        $result = $this->_getLdapClient()->add($dn, $data);
        return ($result instanceof Zend_Ldap);
    }

    public function update($dn, $data)
    {
        $result = $this->_getLdapClient()->update($dn, $data);
        return ($result instanceof Zend_Ldap);
    }

    public function delete($dn)
    {
        $result = $this->_getLdapClient()->delete($dn, false);
        return ($result instanceof Zend_Ldap);
    }

    public function count($filter)
    {
        return $this->_getLdapClient()->count(
            $filter,
            null,
            Zend_Ldap::SEARCH_SCOPE_SUB
        );
    }

    public function exists($dn)
    {
        return $this->_getLdapClient()->exists($dn);
    }

    public function getBaseDn()
    {
        return $this->_getLdapClient()->getBaseDn();
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
}