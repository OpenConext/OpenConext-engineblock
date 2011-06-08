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
 * Cleanup Service for deleting information from the SURFconext platform. E.g. for completely removing a user account
 */
class EngineBlock_Cleanup_CleanupService {

    /**
     * Completely remove a user from the SURFconext platform. Currently this consists of removing the user from the:
     * - LDAP
     *
     * @return void
     */
    public function cleanupUser($uid)
    {
        // Delete the user from the LDAP
        //$this->_deleteLdapUser($uid);

        // Delte the user consent
        $this->_deleteUserConsent($uid);

        // Delete the cookies and session
        $this->_deleteFromEnvironment();
    }

    /**
     * Delete the user from the SURFconext LDAP
     * @param  $uid
     * @return void
     */
    protected function _deleteLdapUser($uid)
    {
        $userDirectory = new EngineBlock_UserDirectory();
        $userDirectory->deleteUser($uid);
    }

    /**
     * Delete the user consent form the database
     *
     * @param  $uid
     * @return void
     */
    protected function _deleteUserConsent($uid)
    {
        $factory = $this->_getDatabaseConnection();

        $query = "DELETE FROM consent
                    WHERE hashed_user_id = ?";
        $parameters = array(
            sha1($this->_getUserId($uid))
        );

        $statement = $factory->prepare($query);
        $statement->execute($parameters);
    }

    protected function _getDatabaseConnection()
    {
        $factory = new EngineBlock_Database_ConnectionFactory();
        return $factory->create(EngineBlock_Database_ConnectionFactory::MODE_WRITE);
    }

    protected function _getUserId($uid)
    {
        $uidParts = explode(":", $uid);
        if (count($uidParts) >= 4) {
            return $uidParts[4];
        }
        return null;
    }

    /**
     * Delete the cookies and environment
     * 
     * @return void
     */
    protected function _deleteFromEnvironment()
    {
        $_COOKIE = array();
        $_SESSION = array();
    }
}