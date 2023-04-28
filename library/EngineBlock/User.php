<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlock\Authentication\Value\SchacHomeOrganization;
use OpenConext\EngineBlock\Authentication\Value\Uid;

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

    public function getAttributes()
    {
        return $this->_attributes;
    }

    public function deleteConsent($spId)
    {
        $pdo = $this->_getDatabaseConnection();

        $query = "DELETE FROM consent
                    WHERE hashed_user_id = ? AND service_id = ?";
        $parameters = array(sha1($this->getUid()), $spId);
        $statement = $pdo->prepare($query);
        $statement->execute($parameters);
    }

    public function getConsent()
    {
        $pdo = $this->_getDatabaseConnection();
        $query = 'SELECT service_id FROM consent
                    WHERE hashed_user_id = ?
                    AND deleted_at IS NULL';
        $parameters = array(
            sha1($this->getUid())
        );
        $statement = $pdo->prepare($query);
        $statement->execute($parameters);
        $resultSet = $statement->fetchAll();

        $result = array();
        foreach($resultSet as $value) {
            $result[] = $value['service_id'];
        }

        return $result;
    }

    /**
     * Completely remove a user from the OpenConext platform.
     *
     * @return void
     */
    public function delete()
    {
        if (!isset($this->_attributes[SchacHomeOrganization::URN_MACE][0])) {
            throw new EngineBlock_Exception(sprintf(
                'Cannot remove user, cannot reliably determine who the user is due to missing "%s" attribute',
                SchacHomeOrganization::URN_MACE
            ));
        }

        $collabPersonId = CollabPersonId::generateWithReplacedAtSignFrom(
            new Uid($this->getUid()),
            new SchacHomeOrganization($this->_attributes[SchacHomeOrganization::URN_MACE][0])
        );

        EngineBlock_ApplicationSingleton::getInstance()
            ->getDiContainer()
            ->getUserDirectory()
            ->deleteUserWith($collabPersonId->getCollabPersonId());

        $this->_deleteUserConsent();

        // Delete the cookies and session
        $this->_deleteFromEnvironment();
    }

    /**
     * Delete the user consent form the database
     *
     * @return void
     */
    protected function _deleteUserConsent()
    {
        $pdo = $this->_getDatabaseConnection();

        $query = "DELETE FROM consent
                    WHERE hashed_user_id = ?";
        $parameters = array(
            sha1($this->getUid())
        );

        $statement = $pdo->prepare($query);
        $statement->execute($parameters);
    }

    /**
     * @return PDO
     */
    protected function _getDatabaseConnection()
    {
        $pdo = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getDatabaseConnectionFactory();
        return $pdo->create();
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
