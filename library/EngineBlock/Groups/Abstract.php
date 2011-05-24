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

class EngineBlock_Groups_Exception_UserDoesNotExist extends EngineBlock_Exception
{
}

abstract class EngineBlock_Groups_Abstract
{
    protected $_stem = NULL;
    
    /**
     * Retrieve the list of groups that the specified subject is a member of.
     * @param String $userIdentifier The user id to get the groups for
     * @return array A list of groups
     */
    abstract public function getGroups($userIdentifier);

    /** 
     * Get the members of a given group
     * @param String $userIdentifier The user id as which to execute the request
     * @param String $groupIdentifier The name of the group to retrieve members of
     * @return array A list of members
     */
    abstract public function getMembers($userIdentifier, $groupIdentifier);

    /**
     * Check whether the given user is a member of the given group.
     * @param String $userIdentifier The user id to check
     * @param String $groupIdentifier The group to check
     * @return boolen
     */
    abstract public function isMember($userIdentifier, $groupIdentifier);
    
    /**
     * Some group provider implementations are able to host more than one set
     * of groups. In many implementations this is called a 'stem', and by
     * setting the stem we can choose which set of groups to use. While we use
     * the term 'stem' here, other implementations are free to implement the
     * filtering as they see fit. Implementations that don't support multiple
     * sets of groups, they can simply ignore this call 
     * @param String $stemIdentifier
     */
    public function setGroupStem($stemIdentifier)
    {
        $this->_stem = $stemIdentifier;
    }
    
    /**
     * Return the stem for this group provider. See setGroupStem for a more
     * elaborate explanation of stems.
     * @return String Stem Identifier
     */
    public function getGroupStem()
    {
        return $this->_stem;
    }
    
}
