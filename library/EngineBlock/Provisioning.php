<?php

class EngineBlock_Provisioning
{
    /**
     * @var EngineBlock_UserDirectory
     */
    protected $_userDirectory = NULL;

    /**
     * @param array $saml2Attributes
     * @return string User Id of provisioned user.
     */
    public function provisionUser(array $saml2Attributes)
    {
        return $this->_getUserDirectory()->registerUser($saml2Attributes);
    }

    protected function _getUserDirectory()
    {
        if ($this->_userDirectory==NULL) {
            $ldapConfig = EngineBlock_ApplicationSingleton::getInstance()
                                                          ->getConfiguration()
                                                          ->ldap;
            $this->_userDirectory = new EngineBlock_UserDirectory($ldapConfig);
        }
        return $this->_userDirectory;
    }

    public function setUserDirectory($userDirectory)
    {
        $this->_userDirectory = $userDirectory;
    }
}
