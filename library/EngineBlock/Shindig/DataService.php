<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class EngineBlock_Shindig_DataService implements ActivityService, PersonService, GroupService
{
    protected $_ebSocialData;

    /**
     * @return EngineBlock_SocialData
     */
    protected function _getSocialData()
    {
        if (!isset($this->_ebSocialData)) {
            
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
     * @throws SocialSpiException
     * @param UserId        $userId
     * @param GroupId       $groupId
     * @param array         $fields    Set of contact fields to return,
     *                                  as array('fieldName' => 'fieldName').
     *                                  This looks weird but it's just the way Shindig
     *                                  passes us the $fields.
     *                                  If $fields['all'] is set, all known fields are returned.
     * @param SecurityToken $token
     * @return array|EmptyResponseItem
     */
    function getPerson($userId, $groupId, $fields, SecurityToken $token)
    {
        if (isset($fields["all"])) {
            $fields = array(); // clear the default fields
        }
        if ($userId->getType() != "userId") {
            throw new SocialSpiException(
                "Relative identifiers such as 'viewer', 'me' or 'owner' not supported! ".
                "(requested: " . ($userId->getType()) . ")"
            );
        }
        $identifier = $userId->getUserId($token);
        $result = $this->_getSocialData()->getPerson(
            $identifier,
            array_values($fields),
            isset($_REQUEST['vo']) ? $_REQUEST['vo'] : null,
            isset($_REQUEST['sp-entity-id']) ? $_REQUEST['sp-entity-id'] : null,
            isset($_REQUEST['subject-id']) ? $_REQUEST['subject-id'] : null
        );
        if (empty($result)) {
            return new EngineBlock_Shindig_Response_EmptyResponseItem();
        }
        return array($result);
    }
    
    /**
     * Returns a list of people that correspond to the passed in person ids.
     *
     * @throws SocialSpiException
     * @param array             $userId     Ids of the people to fetch.
     * @param GroupId           $groupId    Id of the group
     * @param CollectionOptions $options    Request options for filtering/sorting/paging
     * @param array             $fields     Set of contact fields to return, as array('fieldName' => 'fieldName')
     *                                      If $fields['all'] is set, all fields are returned.
     * @param SecurityToken     $token      OAuth Security Token
     * @return EmptyResponseItem|RestfulCollection
     */
    function getPeople($userId, $groupId, CollectionOptions $options, $fields, SecurityToken $token)
    {
        if (isset($fields["all"])) {
            $fields = array(); // clear the default fields
        }

        if ($groupId->getGroupId() === 'self') {
            $fields = array_values($fields);
            $people = array();
            $socialData = $this->_getSocialData();
            foreach ($userId as $userId) {
                $person = $socialData->getPerson(
                    $userId,
                    $fields,
                    isset($_REQUEST['vo']) ? $_REQUEST['vo'] : null,
                    isset($_REQUEST['sp-entity-id']) ? $_REQUEST['sp-entity-id'] : null
                );

                if (!empty($person)) {
                    $people[] = $person;
                }
            }
            if (empty($people)) {
                return new EngineBlock_Shindig_Response_EmptyResponseItem();
            }
        }
        else if ($groupId->getType() === 'all') {
            throw new SocialSpiException("Not implemented by EngineBlock", ResponseError::$INTERNAL_ERROR);
        }
        else {
            if (count($userId) > 1) {
                $message = "Getting the group members for a group given *multiple* uids is not implemented" .
                           " by EngineBlock (try picking one uid)";
                throw new SocialSpiException($message, ResponseError::$INTERNAL_ERROR);
            }

            $groupMemberUid = array_shift($userId);
            /** @var $groupMemberUid UserId */
            $groupMemberUid = $groupMemberUid->getUserId($token);

            $groupId = $groupId->getGroupId();
            $groupId = array_shift($groupId);

            $people = $this->_getSocialData()->getGroupMembers(
                $groupMemberUid,
                $groupId,
                $fields,
                isset($_REQUEST['vo']) ? $_REQUEST['vo'] : null,
                isset($_REQUEST['sp-entity-id']) ? $_REQUEST['sp-entity-id'] : null
            );
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
     * @return ResponseItem Response item with the error code set if there was a problem
     */
    function getPersonGroups($userId, GroupId $groupId, SecurityToken $token)
    {
        $groupId = $groupId->getGroupId();
        if ($groupId && $groupId === 'self') {
            $groupId = null;
        }

        return $this->_getSocialData()->getGroupsForPerson(
            $userId->getUserId($token),
            $groupId,
            isset($_REQUEST['sp-entity-id']) ? $_REQUEST['sp-entity-id'] : null
        );
    }

    public function createActivity($userId, $groupId, $appId, $fields, $activity, SecurityToken $token)
    {
        throw new SocialSpiException("Not implemented by EngineBlock", ResponseError::$INTERNAL_ERROR);
    }

    public function getActivity($userId, $groupId, $appdId, $fields, $activityId, SecurityToken $token)
    {
        throw new SocialSpiException("Not implemented by EngineBlock", ResponseError::$INTERNAL_ERROR);
    }

    public function getActivities($userIds, $groupId, $appId, $sortBy, $filterBy, $filterOp, $filterValue, $startIndex, $count, $fields, $activityIds, $token)
    {
        throw new SocialSpiException("Not implemented by EngineBlock", ResponseError::$INTERNAL_ERROR);
    }

    public function deleteActivities($userId, $groupId, $appId, $activityIds, SecurityToken $token)
    {
        throw new SocialSpiException("Not implemented by EngineBlock", ResponseError::$INTERNAL_ERROR);
    }
} 
