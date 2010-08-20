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
            $this->_ebSocialData = new EngineBlock_SocialData();
        }
        return $this->_ebSocialData;
    }

    /**
     * Returns a Person object for person with $id or false on not found
     *
     * @param UserId $userId
     * @param fields set of contact fields to return, as array('fieldName' => 1)
     * @param security token $token
     */
    function getPerson($userId, $groupId, $fields, SecurityToken $token)
    {
        if ($userId->getType() != "userId") {
            throw new SocialSpiException("Relative identifiers such as 'viewer', 'me' or 'owner' not supported! (requested: " . ($userId->getType()) . ")");
        }
        $identifier = $userId->getUserId($token);
        $result = $this->_getSocialData()->getPerson($identifier, array_values($fields));
        return array($result);
    }

    /**
     * Returns a list of people that correspond to the passed in person ids.
     * @param ids The ids of the people to fetch.
     * @param options Request options for filtering/sorting/paging
     * @param fields set of contact fields to return, as array('fieldName' => 1)
     * @return a list of people.
     */
    function getPeople($userId, $groupId, CollectionOptions $options, $fields, SecurityToken $token)
    {
        $people = array();
        
        $person = new Person('urn:collab:person:surfnet.nl:hansz', 'Hans Zandbelt');
        $people[] = $person;

        $person = new Person('urn:collab:person:surfguest.nl:smibuildings', "SURFmedia Ibuildings User");
        $people[] = $person;

        $person = new Person('urn:collab:person:surfguest.nl:relaxnow', "Boy Baukema");
        $people[] = $person;

        $totalSize = count($people);
        $collection = new RestfulCollection($people, $options->getStartIndex(), $totalSize);
        $collection->setItemsPerPage($options->getCount());
        return $collection;

        // echo "PersonService->getPeople called with arguments: <pre>";
        // var_dump(func_get_args());
        // echo "</pre>";
        // $identifier = $userId->getUserId($token);
        // $result = $this->_getSocialData()->getPersons($identifier)
    }

    /**
     * Fetch groups for a list of ids.
     * @param UserId The user id to perform the action for
     * @param GroupId optional grouping ID 
     * @param token The SecurityToken for this request
     * @return ResponseItem a response item with the error code set if
     * there was a problem
     */
    function getPersonGroups($userId, GroupId $groupId, SecurityToken $token)
    {
        $groups = array();
        $groups[] = array('id'=>'test:a-team', 'title'=>'The A-Team');
        $groups[] = array('id'=>'test:coin', 'title'=>'COIN Dev Team');

        return $groups;
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
