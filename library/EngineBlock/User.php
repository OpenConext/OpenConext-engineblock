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

class EngineBlock_User
{
    private $_attributes = array();

    public function __construct(array $attributes)
    {
        $this->_attributes = $attributes;
    }

    public function getUid()
    {
        return $this->_attributes['nameid'][0];
    }

    public function getDisplayName()
    {
        return $this->_attributes['urn:mace:dir:attribute-def:displayName'][0];
    }

    public function getAttributes()
    {
        return $this->_attributes;
    }

    public function deleteConsent($spId)
    {
        // @todo this is not a factory but a PDO
        $factory = $this->_getDatabaseConnection();

        $query = "DELETE FROM consent
                    WHERE hashed_user_id = ? AND service_id = ?";
        $parameters = array(sha1($this->getUid()), $spId);
        $statement = $factory->prepare($query);
        $statement->execute($parameters);
    }

    public function deleteOauthConsent($consumerKey)
    {
        // @todo this is not a factory but a PDO
        $factory = $this->_getShindigDatabaseConnection();
        $query = 'DELETE FROM oauth_entry WHERE consumer_key = ?
                    AND user_id = ?';
        $parameters = array($consumerKey, $this->getUid());
        $statement = $factory->prepare($query);
        $statement->execute($parameters);
    }

    public function deleteOauthGroupConsent($providerId) {
        // @todo this is not a factory but a PDO
        $factory = $this->_getDatabaseConnection();

        $query = "DELETE FROM group_provider_user_oauth
                    WHERE user_id = ? AND provider_id = ?";
        $parameters = array($this->getUid(), $providerId);
        $statement = $factory->prepare($query);
        $statement->execute($parameters);
    }

    public function getUserOauth()
    {
        // @todo this is not a factory but a PDO
        $factory = $this->_getDatabaseConnection();
        $query = 'SELECT provider_id, user_id FROM group_provider_user_oauth
                    WHERE user_id = ?';
        $parameters = array(
            $this->getUid()
        );

        $statement = $factory->prepare($query);
        $statement->execute($parameters);
        $resultSet = $statement->fetchAll();

        $result = array();
        foreach($resultSet as $value) {
            $result[$value['provider_id']] = $value;
        }

        return $result;
    }

    public function getThreeLeggedShindigOauth()
    {
        // @todo this is not a factory but a PDO
        $factory = $this->_getShindigDatabaseConnection();
        $query = 'SELECT consumer_key FROM oauth_entry
                    WHERE user_id = ?';
        $parameters = array(
            $this->getUid()
        );
        $statement = $factory->prepare($query);
        $statement->execute($parameters);
        $resultSet = $statement->fetchAll();

        $result = array();
        foreach($resultSet as $value) {
            $result[] = $value['consumer_key'];
        }

        return $result;
    }

    public function getConsent()
    {
        // @todo this is not a factory but a PDO
        $factory = $this->_getDatabaseConnection();
        $query = 'SELECT service_id FROM consent
                    WHERE hashed_user_id = ?';
        $parameters = array(
            sha1($this->getUid())
        );
        $statement = $factory->prepare($query);
        $statement->execute($parameters);
        $resultSet = $statement->fetchAll();

        $result = array();
        foreach($resultSet as $value) {
            $result[] = $value['service_id'];
        }

        return $result;
    }

    /**
     * Completely remove a user from the SURFconext platform.
     *
     * @return void
     */
    public function delete()
    {
        $this->_deleteLdapUser();

        $this->_deleteUserConsent();

        $this->_deleteOauthTokens();

        // Delete the cookies and session
        $this->_deleteFromEnvironment();
    }

    /**
     * Delete the user from the SURFconext LDAP.
     *
     * @return void
     */
    protected function _deleteLdapUser()
    {
        $ldapConfig = EngineBlock_ApplicationSingleton::getInstance()
                                                      ->getConfiguration()
                                                      ->ldap;

        $userDirectory = new EngineBlock_UserDirectory($ldapConfig);
        $userDirectory->deleteUser($this->getUid());
    }

    /**
     * Delete the user consent form the database
     *
     * @return void
     */
    protected function _deleteUserConsent()
    {
        // @todo this is not a factory but a PDO
        $factory = $this->_getDatabaseConnection();

        $query = "DELETE FROM consent
                    WHERE hashed_user_id = ?";
        $parameters = array(
            sha1($this->getUid())
        );

        $statement = $factory->prepare($query);
        $statement->execute($parameters);
    }

    protected function _deleteOauthTokens()
    {
        // @todo this is not a factory but a PDO
        $factory = $this->_getDatabaseConnection();

        $query = "DELETE FROM group_provider_user_oauth
                    WHERE user_id = ?";
        $parameters = array(
            $this->getUid()
        );

        $statement = $factory->prepare($query);
        $statement->execute($parameters);
    }

    /**
     * @return PDO
     */
    protected function _getDatabaseConnection()
    {
        $factory = new EngineBlock_Database_ConnectionFactory();
        return $factory->create(EngineBlock_Database_ConnectionFactory::MODE_WRITE);
    }

    protected function _getShindigDatabaseConnection()
    {
        $factory = new EngineBlock_Database_ShindigConnectionFactory();
        return $factory->create(EngineBlock_Database_ConnectionFactory::MODE_WRITE);
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