<?php

class EngineBlock_Groups_Exception_UserDoesNotExist extends EngineBlock_Exception
{
}

abstract class EngineBlock_Groups_Abstract
{
    /**
     * Retrieve the list of groups that the specified subject is a member of.
     */
    abstract public function getGroups($userIdentifier);

    /** 
     * Get the members of a given group
     * @param $userId The user id as which to execute the request
     * @param $groupName The name of the group to retrieve members of
     */
    abstract public function getMembers($userId, $groupName);

    
    
}
