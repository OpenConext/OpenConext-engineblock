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
     * @var EngineBlock_ServiceRegistry_Client
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
                $people[] = $groupMember;
            } else {
                $people[] = $person;
            }
        }
        return $people;
    }

    public function getPerson($identifier, $socialAttributes = array(), $voId = null, $spEntityId = null)
    {
        $fieldMapper = $this->_getFieldMapper();

        $ldapAttributes = $fieldMapper->socialToLdapAttributes($socialAttributes);

        $persons = $this->_getUserDirectory()->findUsersByIdentifier($identifier, $ldapAttributes);
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
                return $groupProvider->getGroupMemberDetails();
            } else {
                return false;
            }
        }
        else {
            //not really posible
            throw new EngineBlock_Exception("More than 1 person found for identifier $identifier");
        }
    }

    protected function _mapEngineBlockGroupToOpenSocialGroup(EngineBlock_Group_Model_Group $group)
    {
        return array(
            'id'            => $group->id,
            'description'   => $group->description,
            'title'         => $group->title,
        );
    }

    protected function _enforceArp($record)
    {
        // @todo not implemented
        return $record;

        // @todo: the below is a pseudocode implementation of an arp enforcer. Not tested.

        // Find out the SP Identifier based on the app id.
        // E.g. if the appid = 'weather.gadget.google.com' and in Janus there is a field
        // called coin:gadgetbaseurl which is set to .*\.gadget\.google\.com, then
        // the SP identifier of that entry will be returned by findIdentifiersBymetadata.
        $result = $this->_getServiceRegistry()->
                         findIdentifiersByMetadata(ENGINEBLOCK_SERVICEREGISTRY_GADGETBASEURL_FIELD,
                                                   $this->_appId);

        if (count($result)) {
            $spIdentifier = $result[0];
            $arp = $this->_getServiceRegistry()->getArp($spIdentifier);

            // @todo filter attributes based on arp.

            return $record;

        }

        return array(); // something went wrong, as a precaution we don't give back any data
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
            $this->_groupProvider = EngineBlock_Group_Provider_Aggregator_MemoryCacheProxy::createFromConfigFor($userId);
        }
        return $this->_groupProvider;
    }

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
     * @return EngineBlock_ServiceRegistry_Client
     */
    protected function _getServiceRegistry()
    {
        if ($this->_serviceRegistry == NULL) {
            $this->_serviceRegistry = new EngineBlock_ServiceRegistry_CacheProxy();
        }
        return $this->_serviceRegistry;
    }

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

    public function setFieldMapper($mapper)
    {
        $this->_fieldMapper = $mapper;
    }
}
