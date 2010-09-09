<?php
class EngineBlock_OpenSocial_ShindigService implements ActivityService, PersonService, GroupService
{
    protected $_ebSocialData = NULL;

    /**
     * @return EngineBlock_SocialData
     */
    protected function _getSocialData()
    {   
        // hmm, we can't do dependency injection here since Shindig instantiates our 
        // EngineBlock_OpenSocial_ShindigService for us.
        if (is_null($this->_ebSocialData)) {
            
            // @todo This is a hack.. do we have a cleaner way to retrieve the appId?
            $appId = NULL;
            if (isset($_REQUEST["appid"])) {
                $appId = $_REQUEST["appid"]; 
            } 
            
            $this->_ebSocialData = new EngineBlock_SocialData($appId);
        }
        return $this->_ebSocialData;
    }

    /**
     * Returns a Person object for person with $id or false on not found
     *
     * @param UserId $userId
     * @param fields set of contact fields to return, as array('fieldName' => 
     *                  'fieldName'). This looks weird but it's just the way
     *                  Shindig passes us the $fields. If $fields['all'] is  
     *                  set, all known fields are returned.
     * @param security token $token
     */
    function getPerson($userId, $groupId, $fields, SecurityToken $token)
    {
        if (isset($fields["all"])) {
            $fields = array(); // clear the default fields
        }
        if ($userId->getType() != "userId") {
            throw new SocialSpiException("Relative identifiers such as 'viewer', 'me' or 'owner' not supported! (requested: " . ($userId->getType()) . ")");
        }
        $identifier = $userId->getUserId($token);
        $result = $this->_getSocialData()->getPerson($identifier, array_values($fields));
        return array($result);
    }

    /**
     * Returns a list of people that correspond to the passed in person ids.
     * @param array $userId The ids of the people to fetch.
     * @param GroupId $groupId The id of the group
     * @param options Request options for filtering/sorting/paging
     * @param fields set of contact fields to return, as array('fieldName' => 'fieldName')
     *        If $fields['all'] is set, all fields are returned.
     * @return a list of people.
     */
    function getPeople($userId, $groupId, CollectionOptions $options, $fields, SecurityToken $token)
    {
      if (isset($fields["all"])) {
          $fields = array(); // clear the default fields
      }
        
        if ($groupId->getGroupId()!=='self') {
            if (count($userId) > 1) {
                $message = "Getting the group members for a group given *multiple* uids is not implemented by EngineBlock (try picking one uid)";
                throw new SocialSpiException($message, ResponseError::$INTERNAL_ERROR);
            }

            $groupMemberUid = array_shift($userId);
            $groupMemberUid = $groupMemberUid->getUserId($token);

            $groupId = $groupId->getGroupId();
            $groupId = array_shift($groupId);

            $people = $this->_getSocialData()->getGroupMembers($groupMemberUid, $groupId, $fields);
        }
        else {
            $fields = array_values($fields);
            $people = array();
            $socialData = $this->_getSocialData();
            foreach ($userId as $userId) {
                $people[] = $socialData->getPerson($userId, $fields);
            }
        }

        $totalSize = count($people);
        $collection = new RestfulCollection($people, $options->getStartIndex(), $totalSize);
        $collection->setItemsPerPage($options->getCount());
        return $collection;
    }

    /**
     * Fetch groups for a list of ids.
     * @param UserId        $userId     The user id to perform the action for
     * @param GroupId       $groupId    Optional grouping ID
     * @param SecurityToken $token      The SecurityToken for this request
     * @return ResponseItem a response item with the error code set if
     * there was a problem
     */
    function getPersonGroups($userId, GroupId $groupId, SecurityToken $token)
    {
        $groupId = $groupId->getGroupId();
        if ($groupId && $groupId !== 'self') {
            $message = "Getting person groups for a given group is not implemented by EngineBlock";
            throw new SocialSpiException($message, ResponseError::$INTERNAL_ERROR);
        }

        return $this->_getSocialData()->getGroupsForPerson($userId->getUserId($token));
    }

    public function getActivities($userIds, $groupId, $appId, $sortBy, $filterBy, $filterOp, $filterValue, $startIndex, $count, $fields, $activityIds, $token)
    {
        throw new SocialSpiException("Not implemented by EngineBlock", ResponseError::$INTERNAL_ERROR);
    }

    public function getActivity($userId, $groupId, $appdId, $fields, $activityId, SecurityToken $token)
    {
        throw new SocialSpiException("Not implemented by EngineBlock", ResponseError::$INTERNAL_ERROR);
    }

    public function deleteActivities($userId, $groupId, $appId, $activityIds, SecurityToken $token)
    {
        throw new SocialSpiException("Not implemented by EngineBlock", ResponseError::$INTERNAL_ERROR);
    }

    /**
     * Creates the passed in activity for the given user. Once createActivity is
     * called, getActivities will be able to return the Activity.
     */
    public function createActivity($userId, $groupId, $appId, $fields, $activity, SecurityToken $token)
    {
        throw new SocialSpiException("Not implemented by EngineBlock", ResponseError::$INTERNAL_ERROR);
    }
} 
