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

/**
 * Todo this only supports basic functionality, for more see:
 * @url https://spaces.internet2.edu/display/Grouper/Grouper+Web+Services#GrouperWebServices-Operations
 */
interface Grouper_Client_Interface
{
    /**
     * Set the subjectId on behalf of which and for which all requests are done.
     *
     * @abstract
     * @param  $subjectId
     * @return Grouper_Client_Interface
     */
    public function setSubjectId($subjectId);

    /**
     * Retrieve the list of groups that the specified subject is a member of.
     *
     * @abstract
     * @return array
     */
    public function getGroups($stem = null);

    /**
     * @abstract
     * @param string $groupName
     * @return void
     */
    public function getMembers($groupName);

    /**
     * Retrieve the member privileges for a specified subjectId and a specified group
     *
     * @abstract
     * @param $subjectId
     * @param $groupName
     * @return array
     */
    public function getMemberPrivileges($subjectId, $groupName);

    /**
     * @abstract
     * @param string $groupName
     * @return bool
     */
    public function hasMember($groupName);

    /**
     * Delete a group membership for a subject
     *
     * @abstract
     * @param $subjectId
     * @param $groupName
     * @return bool
     */
    public function deleteMembership($subjectId, $groupName);
}
