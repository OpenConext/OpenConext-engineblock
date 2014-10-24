<?php

class EngineBlock_Test_UserDirectoryMock extends EngineBlock_UserDirectory
{
    protected $_users = array();

    public function __construct()
    {
    }

    public function setUser($id, $user)
    {
        $this->_users[$id] = $user;
        return $this;
    }

    public function findUsersByIdentifier($identifier, $ldapAttributes = array())
    {
        return array($this->_users[$identifier]);
    }
}
