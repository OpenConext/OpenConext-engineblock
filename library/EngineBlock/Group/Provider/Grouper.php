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

class EngineBlock_Group_Provider_Grouper extends EngineBlock_Group_Provider_Abstract
{
    /**
     * @var Grouper_Client_Interface
     */
    protected $_grouperClient;

    /**
     * Factory method to create and configure a group provider from it's given configuration
     *
     * @static
     * @abstract
     * @param Zend_Config $config
     * @param string $userId
     * @return EngineBlock_Group_Provider_Grouper
     */
    public static function createFromConfigs(Zend_Config $config, $userId)
    {
        $grouperClient = Grouper_Client_Rest::createFromConfig($config);
        $provider = new self($grouperClient);
        $provider->setUserId($userId);

        $provider->configurePreconditions($config);
        $provider->configureGroupFilters($config);
        $provider->configureGroupMemberFilters($config);

        return $provider;
    }

    public function __construct(Grouper_Client_Interface $grouperClient)
    {
        $this->_grouperClient = $grouperClient;
    }

    public function setUserId($userId)
    {
        parent::setUserId($userId);

        $this->_grouperClient->setSubjectId($userId);
        return $this;
    }

    public function getGroups()
    {
        $grouperGroups = $this->_grouperClient->getGroups();

        $groups = array();
        foreach ($grouperGroups as $group) {
            $groups[] = $this->_mapGrouperGroupToEngineBlockGroup($group);
        }
        return $groups;
    }

    public function getMembers($groupIdentifier)
    {
        $subjects = $this->_grouperClient->getMembers($this->_getStemmedGroupId($groupIdentifier));

        $members = array();
        foreach ($subjects as $subject) {
            $members[] = $this->_mapGrouperSubjectToEngineBlockGroupMember($subject);
        }
        return $members;
    }

    /**
     * Check whether the given user is a member of the given group.
     * @param String $userIdentifier The user id to check
     * @param String $groupIdentifier The group to check
     * @return boolean
     */
    public function isMember($groupIdentifier)
    {
        return $this->_grouperClient->hasMember($this->_getStemmedGroupId($groupIdentifier));
    }

    protected function _mapGrouperGroupToEngineBlockGroup(Grouper_Model_Group $grouperGroup)
    {
        $engineBlockGroup = new EngineBlock_Group_Model_Group();
        $engineBlockGroup->id           = $grouperGroup->name;
        $engineBlockGroup->title        = $grouperGroup->displayExtension;
        $engineBlockGroup->description  = $grouperGroup->description;
        return $engineBlockGroup;
    }

    protected function _mapGrouperSubjectToEngineBlockGroupMember(Grouper_Model_Subject $grouperSubject)
    {
        $engineBlockMember = new EngineBlock_Group_Model_GroupMember();
        $engineBlockMember->id = $grouperSubject->id;
        return $engineBlockMember;
    }
}
