<?php

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
        EngineBlock_ApplicationSingleton::getInstance()
          ->getDiContainer()
            ->getUserDirectory()
            ->deleteUser($this->getUid());
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
        $pdo = new EngineBlock_Database_ConnectionFactory();
        return $pdo->create(EngineBlock_Database_ConnectionFactory::MODE_WRITE);
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
