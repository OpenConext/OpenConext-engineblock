<?php

class EngineBlock_Provisioning
{
    /**
     * @var EngineBlock_UserDirectory
     */
    protected $_userDirectory = NULL;
    
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
    
    public function provisionUser($uid, $attributes) {
        //$this->_getUserDirectory()->findUsersByIdentifier($uid);

        //return $attributes;
        return array();
    }

    protected function _getAttributesHash()
    {
        
    }
}
