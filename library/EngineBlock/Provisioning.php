<?php

class EngineBlock_Provisioning
{
    /**
     * @var EngineBlock_UserDirectory
     */
    protected $_userDirectory = NULL;

    /**
     * @param  $saml2Attributes
     * @return array Collaboration attributes to add after consent
     */
    public function provisionUser(array $saml2Attributes, $idpEntityMetadata)
    {
        return $this->_getUserDirectory()->registerUser($saml2Attributes, $idpEntityMetadata);
    }

    protected function _getUserDirectory()
    {
        if ($this->_userDirectory==NULL) {
            $this->_userDirectory = new EngineBlock_UserDirectory();
        }
        return $this->_userDirectory;
    }

    public function setUserDirectory($userDirectory)
    {
        $this->_userDirectory = $userDirectory;
    }
}
