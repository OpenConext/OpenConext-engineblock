<?php

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
                    WHERE hashed_user_id = ?';
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
        $this->_deleteLdapUser();

        $this->_deleteUserConsent();

        $this->_deleteOauthTokens();

        // Delete the cookies and session
        $this->_deleteFromEnvironment();
    }

    /**
     * Delete the user from the user table
     *
     * @throws EngineBlock_Exception
     */
    protected function _deleteLdapUser()
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

    protected function _deleteOauthTokens()
    {
        $pdo = $this->_getDatabaseConnection();

        $query = "DELETE FROM group_provider_user_oauth
                    WHERE user_id = ?";
        $parameters = array(
            $this->getUid()
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
