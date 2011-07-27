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

interface EngineBlock_Group_Provider_Interface
{
    /**
     * Factory method to create and configure a group provider from it's given configuration
     *
     * @static
     * @abstract
     * @param Zend_Config $config Configuration for this provider in it's specific format
     * @param string      $userId UserId to provide group information for (required)
     * @return EngineBlock_Group_Provider_Interface
     */
    public static function createFromConfigs(Zend_Config $config, $userId);

    /**
     * Get the ID of this provider (plain alphanumeric string for use in configs, URLs and such)
     *
     * @abstract
     * @return string
     */
    public function getId();

    /**
     * Set the ID of the User to provide group information for
     *
     * @abstract
     * @param string $userId
     * @return EngineBlock_Group_Provider_Interface
     */
    public function setUserId($userId);

    /**
     * Get the display name for this group provider
     *
     * @abstract
     * @return string
     */
    public function getDisplayName();

    /**
     * Get the ID of the User that group information will be provided for
     *
     * @abstract
     * @return string
     */
    public function getUserId();

    /**
     * Add a precondition for use of this provider.
     *
     * @abstract
     * @param string $className
     * @param Zend_Config|null $options
     * @return EngineBlock_Group_Provider_Interface
     */
    public function addPrecondition($className, $options = null);

    /**
     * Get the preconditions for use of this provider.
     *
     * @abstract
     * @return array
     */
    public function getPreconditions();

    /**
     * Validate that this providers meets the preconditions, if this returns false, then you SHOULD not
     * use this provider.
     *
     * Mainly used for checking that a Provider is applicable for a given userid.
     *
     * @abstract
     * @return bool
     */
    public function validatePreconditions();

    public function removePreconditionByClassName($className);

    public function addGroupFilter(EngineBlock_Group_Provider_Filter_Interface $filter);

    public function getGroupFilters();

    public function addMemberFilter(EngineBlock_Group_Provider_Filter_Interface $filter);

    public function getMemberFilters();

    /**
     * Retrieve the list of groups that the specified subject is a member of.
     * @return array A list of groups
     */
    public function getGroups();

    /**
     * Get the members of a given group
     * @param String $groupIdentifier The name of the group to retrieve members of
     * @return array A list of members
     */
    public function getMembers($groupIdentifier);

    /**
     * Check whether the given user is a member of the given group.
     * @param String $groupIdentifier The group to check
     * @return boolean
     */
    public function isMember($groupIdentifier);

    /**
     * Check whether the current user is in a group in the current stem.
     *
     * @example: User urn:collab:person:example.edu:john.doe is in
     * the groups: urn:collab:group:internet2.edu:grouper,
     * urn:collab:group:surfnet.nl:grouper
     * Given a stem of urn:collab:group:internet2.edu this method will return true.
     * Given a stem of urn:collab:group:janet.edu this method will return false.
     *
     * @param String $groupIdentifier The group to check
     * @return boolean
     */
    public function isMemberInStem();

    /**
     * @abstract
     * @return string
     */
    public function getGroupStem();

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
    public function setGroupStem($stemIdentifier);
}