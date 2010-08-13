<?php
class EngineBlock_SocialData
{
    protected $_userDirectory = NULL;

    public function getPerson($identifier, $fields = array())
    {
        $result = array();
        $persons = $this->_getUserDirectory()->findUsersByIdentifier($identifier, $fields);
        if (count($persons)) {
            // ignore the hypothetical possibility that we get multiple results for now.
            $result = $persons[0];
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
}