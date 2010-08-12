<?php

class EngineBlock_Provisioning
{
    protected $_userDirectory = NULL;
    
    protected function _getUserDirectory()
    {
        if ($this->$_userDirectory==NULL) {
            $this->$_userDirectory = new EngineBlock_UserDirectory();
        }
        return $this->$_userDirectory;
    }

    public function setUserDirectory($userDirectory)
    {
        $this->$_userDirectory = $userDirectory;     
    }
    
    public function provisionUser($attributes, $attributeHash) {
        //$this->_getUserDirectory();
        //$info = array();
        return TRUE;
    }
}
