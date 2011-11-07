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

define('ENGINEBLOCK_SERVICEREGISTRY_GADGETBASEURL_FIELD', 'coin:gadgetbaseurl');

class EngineBlock_SocialData
{
    /**
     * @var EngineBlock_UserDirectory
     */
    protected $_userDirectory = NULL;

    /**
     * @var EngineBlock_Groups_Provider_Abstract
     */
    protected $_groupProvider = null;

    /**
     * @var Janus_Client
     */
    protected $_serviceRegistry = NULL;

    /**
     * @var EngineBlock_SocialData_FieldMapper
     */
    protected $_fieldMapper = NULL;

    /**
     * @var EngineBlock_AttributeAggregator
     */
    protected $_attributeAggregator;

    /**
     * @var String
     */
    protected $_appId = NULL;
    
    /**
     * Construct an EngineBlock_SocialData instance for retrieving social data within
     * Engineblock
     * @param String $appId The id of the app on behalf of which we are retrieving data
     */
    public function __construct($appId)
    {
        $this->_appId = $appId;
    }

    /**
     * @param string $identifier
     * @param null|string $groupId
     * @param null|string $voId
     * @return array OpenSocial groups
     */
    public function getGroupsForPerson($identifier, $groupId = null, $voId = null)
    {
        $engineBlockGroups = NULL;
        $groupProvider = $this->_getGroupProvider($identifier);
        if ($voId) {
            $virtualOrganization = new EngineBlock_VirtualOrganization($voId);
            $groupStem = $virtualOrganization->getStem();
            $engineBlockGroups = $groupProvider->getGroupsByStem($groupStem);
        }
        else {
            $engineBlockGroups = $groupProvider->getGroups();
        }

        $openSocialGroups = array();
        foreach ($engineBlockGroups as $group) {
             $openSocialGroup = $this->_mapEngineBlockGroupToOpenSocialGroup($group);

             if ($groupId && $openSocialGroup['id'] !== $groupId) {
                continue;
             }

             $openSocialGroups[] = $openSocialGroup;
        }
        return $openSocialGroups;
    }

    /**
     * @param $groupMemberUid
     * @param $groupId
     * @param array $socialAttributes
     * @param null $voId
     * @param null $spEntityId
     * @return array
     */
    public function getGroupMembers($groupMemberUid, $groupId, $socialAttributes = array(), $voId = null, $spEntityId = null)
    {
        $groupMembers = $this->_getGroupProvider($groupMemberUid)->getMembers($groupId);
        
        $people = array();
        /**
         * @var EngineBlock_Group_Model_GroupMember $groupMember
         */
        foreach ($groupMembers as $groupMember) {
            $person = $this->getPerson($groupMember->id, $socialAttributes, $voId, $spEntityId);
            if (!$person) {
                $people[] = $this->_mapEngineBlockGroupMemberToOpenSocialGroupMember($groupMember);
            } else {
                $person['voot_membership_role'] = $groupMember->userRole;
                $people[] = $person;
            }
        }
        return $people;
    }

    /**
     * @throws EngineBlock_Exception
     * @param $identifier
     * @param array $socialAttributes
     * @param string|null $voId
     * @param string|null $spEntityId
     * @param string|null $subjectId
     * @return array|bool|mixed|the
     */
    public function getPerson($identifier, $socialAttributes = array(), $voId = null, $spEntityId = null, $subjectId = null)
    {
        $fieldMapper = $this->_getFieldMapper();

        $ldapAttributes = $fieldMapper->socialToLdapAttributes($socialAttributes);
        $searchId = (($subjectId) ? $subjectId : $identifier);
        $persons = $this->_getUserDirectory()->findUsersByIdentifier($searchId, $ldapAttributes);
        if (count($persons) === 1) {
            $person = array_shift($persons);
            $person = $fieldMapper->ldapToSocialData($person, $socialAttributes);

            if ($voId && $spEntityId) {
                $person = $this->_getAttributeAggregator($voId, $spEntityId)->aggregateFor(
                    $person,
                    $person['id'],
                    EngineBlock_AttributeAggregator::FORMAT_OPENSOCIAL
                );
            }

            // Make sure we only include attributes that we are allowed to share
            $person = $this->_enforceArp($person);
            
            return $person;
        } else if (count($persons) === 0) {
            // We need to see if the user might 'exists' in an ExternalGroup provider
            $groupProvider = $this->_getGroupProvider($identifier);
            if ($groupProvider->isGroupProviderForUser()) {
                return $groupProvider->getGroupMemberDetails($subjectId);
            } else {
                return false;
            }
        }
        else {
            // Not really possible
            throw new EngineBlock_Exception("More than 1 person found for identifier $identifier");
        }
    }

    /**
     * @param EngineBlock_Group_Model_Group $group
     * @return array
     */
    protected function _mapEngineBlockGroupToOpenSocialGroup(EngineBlock_Group_Model_Group $group)
    {
        return array(
            'id'            => $group->id,
            'description'   => $group->description,
            'title'         => $group->title,
            'voot_membership_role' => $group->userRole,
        );
    }

    protected function _mapEngineBlockGroupMemberToOpenSocialGroupMember(EngineBlock_Group_Model_GroupMember $member)
    {
        return array(
            'id'                    => $member->id,
            'displayName'           => $member->displayName,
            'voot_membership_role'  => $member->userRole
        );
    }

    /**
     * Enforce Attribute Release Policy
     *
     * @todo not implemented
     *
     * @param $record
     * @return array
     */
    protected function _enforceArp(array $record)
    {
        return $record;
    }

    /**
     * @return EngineBlock_UserDirectory
     */
    protected function _getUserDirectory()
    {
        if ($this->_userDirectory == NULL) {
            $ldapConfig = EngineBlock_ApplicationSingleton::getInstance()
                                                          ->getConfiguration()
                                                          ->ldap;
            $this->_userDirectory = new EngineBlock_UserDirectory($ldapConfig);
        }
        return $this->_userDirectory;
    }

    /**
     * @param $userDirectory
     */
    public function setUserDirectory($userDirectory)
    {
        $this->_userDirectory = $userDirectory;
    }

    /**
     * @param string $userId Id of the user (urn:collab:...)
     * @return EngineBlock_Group_Provider_Abstract
     */
    protected function _getGroupProvider($userId)
    {
        if (!isset($this->_groupProvider)) {
            $this->_groupProvider = EngineBlock_Group_Provider_Aggregator_MemoryCacheProxy::createFromDatabaseFor($userId);
        }
        return $this->_groupProvider;
    }

    /**
     * @param $voId
     * @param $spEntityId
     * @return EngineBlock_AttributeAggregator
     */
    protected function _getAttributeAggregator($voId, $spEntityId)
    {
        if (!isset($this->_attributeAggregator)) {
            $this->_attributeAggregator = new EngineBlock_AttributeAggregator(
                array(
                    new EngineBlock_AttributeProvider_VoManage($voId, $spEntityId),
                )
            );
        }
        return $this->_attributeAggregator;
    }
    
    /**
     * @return Janus_Client
     */
    protected function _getServiceRegistry()
    {
        if ($this->_serviceRegistry == NULL) {
            $this->_serviceRegistry = new Janus_Client_CacheProxy();
        }
        return $this->_serviceRegistry;
    }

    /**
     * @param $serviceRegistry
     */
    public function setServiceRegistry($serviceRegistry)
    {
        $this->_serviceRegistry = $serviceRegistry;
    }
    
    /**
     * @return EngineBlock_SocialData_FieldMapper mapper
     */
    protected function _getFieldMapper()
    {
        if ($this->_fieldMapper == NULL) {
            $this->_fieldMapper = new EngineBlock_SocialData_FieldMapper();
        }
        return $this->_fieldMapper;
    }

    /**
     * @param $mapper
     */
    public function setFieldMapper($mapper)
    {
        $this->_fieldMapper = $mapper;
    }
}
