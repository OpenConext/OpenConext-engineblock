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
     * @var EngineBlock_Group_Provider_Interface
     */
    protected $_provider;

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
    public function getGroups()
    {
        $this->_provider->setUserId($this->_userIdReplaced);
        $results = $this->_provider->getGroups();
        $this->_provider->setUserId($this->_userId);

        return $results;
    }

    /**
     * Get the members of a given group
     * @param String $groupIdentifier The name of the group to retrieve members of
     * @return array A list of members
     */
    public function getMembers($groupIdentifier)
    {
        $this->_provider->setUserId($this->_userIdReplaced);
        $results = $this->_provider->getMembers($groupIdentifier);
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
        $this->_provider->setUserId($this->_userIdReplaced);
        $results = $this->_provider->isMember($groupIdentifier);
        $this->_provider->setUserId($this->_userId);

        return $results;
    }

    // DIRECTLY PROXY ALL OTHER METHODS

    /**
     * Get the ID of this provider (plain alphanumeric string for use in configs, URLs and such)
     *
     * @abstract
     * @return string
     */
    public function getId()
    {
        return $this->_provider->getId();
    }

    /**
     * Get the display name for this group provider
     *
     * @abstract
     * @return string
     */
    public function getDisplayName()
    {
        return $this->_provider->getDisplayName();
    }

    /**
     * Get the ID of the User that group information will be provided for
     *
     * @abstract
     * @return string
     */
    public function getUserId()
    {
        return $this->_provider->getUserId();
    }

    /**
     * Add a precondition for use of this provider.
     *
     * @abstract
     * @param string $className
     * @param array $options
     * @return EngineBlock_Group_Provider_Interface
     */
    public function addPrecondition($className, $options = null)
    {
        return $this->_provider->addPrecondition($className, $options);
    }

    /**
     * Get the preconditions for use of this provider.
     *
     * @abstract
     * @return array
     */
    public function getPreconditions()
    {
        return $this->_provider->getPreconditions();
    }

    /**
     * Validate that this providers meets the preconditions, if this returns false, then you SHOULD not
     * use this provider.
     *
     * Mainly used for checking that a Provider is applicable for a given userid.
     *
     * @abstract
     * @return bool
     */
    public function validatePreconditions()
    {
        return $this->_provider->validatePreconditions();
    }

    public function addGroupFilter(EngineBlock_Group_Provider_Filter_Interface $filter)
    {
        return $this->_provider->addGroupFilter($filter);
    }

    public function getGroupFilters()
    {
        return $this->_provider->getGroupFilters();
    }

    public function addMemberFilter(EngineBlock_Group_Provider_Filter_Interface $filter)
    {
        return $this->_provider->addMemberFilter($filter);
    }

    public function getMemberFilters()
    {
        return $this->_provider->getMemberFilters();
    }

    /**
     * @abstract
     * @return string
     */
    public function getGroupStem()
    {
        return $this->_provider->getGroupStem();
    }

    /**
     * Some group provider implementations are able to host more than one set
     * of groups. In many implementations this is called a 'stem', and by
     * setting the stem we can choose which set of groups to use. While we use
     * the term 'stem' here, other implementations are free to implement the
     * filtering as they see fit. Implementations that don't support multiple
     * sets of groups, they can simply ignore this call
     * @param String $stemIdentifier
     * @return EngineBlock_Group_Provider_Interface
     */
    public function setGroupStem($stemIdentifier)
    {
        return $this->_provider->setGroupStem($stemIdentifier);
    }

    /**
     * Proxy calls that are not in the interface (like the OAuth / OpenSocial specific stuff)
     * to the provider;
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->_provider, $name), $arguments);
    }
}