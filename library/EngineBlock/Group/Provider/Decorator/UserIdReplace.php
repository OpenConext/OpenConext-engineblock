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

class EngineBlock_Group_Provider_Decorator_UserIdReplace
    extends EngineBlock_Group_Provider_Decorator_Abstract
{
    /**
     * @var string
     */
    protected $_search;

    /**
     * @var string
     */
    protected $_replace;

    /**
     * @var string
     */
    protected $_userId;

    /**
     * @var string
     */
    protected $_userIdReplaced;

    public static function createFromConfigsWithProvider(EngineBlock_Group_Provider_Interface $provider, Zend_Config $config)
    {
        if (!isset($config->search) || !isset($config->replace)) {
            throw new EngineBlock_Group_Provider_Exception(
                "Missing configuration for UserIdReplace decorator, please make sure .search and .replace are set"
            );
        }
        return new self($provider, $config->search, $config->replace);
    }

    public function __construct($provider, $search, $replace)
    {
        $this->_provider = $provider;
        $this->_search   = $search;
        $this->_replace  = $replace;

        $this->_loadReplacedUserId();
    }

    protected function _loadReplacedUserId()
    {
        $this->_userId = $this->_provider->getUserId();
        $this->_userIdReplaced = preg_replace($this->_search, $this->_replace, $this->_userId);
    }

    /**
     * Set the ID of the User to provide group information for
     *
     * @abstract
     * @param string $userId
     * @return EngineBlock_Group_Provider_Interface
     */
    public function setUserId($userId)
    {
        $this->_provider->setUserId($userId);
        $this->_loadReplacedUserId();
        return $this;
    }

    /**
     * Retrieve the list of groups that the specified subject is a member of.
     * @return array A list of groups
     */
    public function getGroups($serviceProviderGroupAcls)
    {
        // If the conversion didn't do anything, it's probably not a user for this group provider
        // so just return an empty array
        if ($this->_search && $this->_userId === $this->_userIdReplaced) {
            return array();
        }

        $this->_provider->setUserId($this->_userIdReplaced);
        $results = $this->_provider->getGroups($serviceProviderGroupAcls);
        $this->_provider->setUserId($this->_userId);

        return $results;
    }

    /**
     * Retrieve the list of groups that the specified subject is a member of.
     * @param string $stem
     * @return array A list of groups
     */
    public function getGroupsByStem($stem, $serviceProviderGroupAcls)
    {
        // If the conversion didn't do anything, it's probably not a user for this group provider
        // so just return an empty array
        if ($this->_search && $this->_userId === $this->_userIdReplaced) {
            return array();
        }

        $this->_provider->setUserId($this->_userIdReplaced);
        $results = $this->_provider->getGroupsByStem($stem, $serviceProviderGroupAcls);
        $this->_provider->setUserId($this->_userId);

        return $results;
    }

    /**
     * Get the members of a given group
     * @param String $groupIdentifier The name of the group to retrieve members of
     * @return array A list of members
     */
    public function getMembers($groupIdentifier, $serviceProviderGroupAcls)
    {
        // If the conversion didn't do anything, it's probably not a user for this group provider
        // so just return an empty array
        if ($this->_search && $this->_userId === $this->_userIdReplaced) {
            return array();
        }

        $this->_provider->setUserId($this->_userIdReplaced);
        $results = $this->_provider->getMembers($groupIdentifier, $serviceProviderGroupAcls);
        $this->_provider->setUserId($this->_userId);

        return $results;
    }

    /**
     * Check whether the given user is a member of the given group.
     * @param String $groupIdentifier The group to check
     * @return boolean
     */
    public function isMember($groupIdentifier)
    {
        // If the conversion didn't do anything, it's probably not a user for this group provider
        // so just return an empty array
        if ($this->_search && $this->_userId === $this->_userIdReplaced) {
            return false;
        }

        $this->_provider->setUserId($this->_userIdReplaced);
        $results = $this->_provider->isMember($groupIdentifier);
        $this->_provider->setUserId($this->_userId);

        return $results;
    }

    /**
     * Get the details of a groupMember
     * @param string $subjectId User to get the data for
     * @return array Group member data
     */
    public function getGroupMemberDetails($subjectId = null)
    {
        // If the conversion didn't do anything, it's probably not a user for this group provider
        // so just return an empty array
        if ($this->_search && $this->_userId === $this->_userIdReplaced) {
            return array();
        }

        $this->_provider->setUserId($this->_userIdReplaced);
        $subjectIdReplaced = null;
        if ($subjectId) {
            $subjectIdReplaced = preg_replace($this->_search, $this->_replace, $subjectId);
        }
        $results = $this->_provider->getGroupMemberDetails($subjectIdReplaced);
        $this->_provider->setUserId($this->_userId);

        return $results;
    }
}