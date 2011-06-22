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

abstract class EngineBlock_Group_Provider_OpenSocial_Abstract
    extends EngineBlock_Group_Provider_Abstract
{
    /**
     * @var OpenSocial_Rest_Client
     */
    protected $_openSocialRestClient;

    public function __construct($id, $name, $openSocialRestClient)
    {
        $this->_id = $id;
        $this->_name = $name;
        $this->_openSocialRestClient = $openSocialRestClient;
    }

    /**
     * Retrieve the list of groups that the specified subject is a member of.
     * @return array A list of groups
     */
    public function getGroups()
    {
        $openSocialGroups = $this->_openSocialRestClient->get(
            '/groups/{uid}',
            array(
                'uid' => $this->_userId,
            )
        );
        return $openSocialGroups;
    }

    /**
     * Get the members of a given group
     * @param String $groupIdentifier The name of the group to retrieve members of
     * @return array A list of members
     */
    public function getMembers($groupIdentifier)
    {
        $openSocialPeople = $this->_openSocialRestClient->get(
            '/people/{uid}/{gid}',
            array(
                'uid' => $this->_userId,
                'gid' => $groupIdentifier,
            )
        );
        return $openSocialPeople;
    }

    /**
     * Check whether the given user is a member of the given group.
     * @param String $groupIdentifier The group to check
     * @return boolean
     */
    public function isMember($groupIdentifier)
    {
        $members = $this->getMembers($groupIdentifier);

        foreach ($members as $member) {
            if ($member->id === $this->_userId) {
                return true;
            }
        }
        
        return false;
    }
}