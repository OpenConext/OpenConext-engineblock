<?php
class EngineBlock_SocialData
{
    protected $_userDirectory = NULL;
    protected $_fieldMapper = NULL;
 
    public function getPerson($identifier, $socialAttributes = array())
    {
        $result = array();
                
        $ldapAttributes = $this->_getFieldMapper()->socialToLdapAttributes($socialAttributes);
        
        $persons = $this->_getUserDirectory()->findUsersByIdentifier($identifier, $ldapAttributes);
        if (count($persons)) {
            // ignore the hypothetical possibility that we get multiple results for now.
            $result = $this->_getFieldMapper()->ldapToSocialData($persons[0], $socialAttributes);
        }
          
        return $result;
    }

    /**
     * @return EngineBlock_UserDirectory
     */
    protected function _getUserDirectory()
    {
        if ($this->_userDirectory == NULL) {
            $this->_userDirectory = new EngineBlock_UserDirectory();
        }
        return $this->_userDirectory;
    }

    public function setUserDirectory($userDirectory)
    {
        $this->_userDirectory = $userDirectory;
    }
    
    /**
     * @return EngineBlock_UserDirectory_FieldMapper mapper
     */
    protected function _getFieldMapper()
    {
        if ($this->_fieldMapper == NULL) {
            $this->_fieldMapper = new EngineBlock_SocialData_FieldMapper();
        }
        return $this->_fieldMapper;
    }

    public function setFieldMapper($mapper)
    {
        $this->_fieldMapper = $mapper;
    }
}
