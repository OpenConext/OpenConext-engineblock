<?php

class EngineBlock_Group_Provider_OpenSocial_Oauth_Helper_AccessToken
{
    protected $_providerId;
    protected $_connection;
    protected $_userId;

    public function __construct($providerId, PDO $databaseConnection, $userId)
    {
        $this->_providerId = $providerId;
        $this->_connection = $databaseConnection;
        $this->_userId     = $userId;
    }

    public function loadAccessToken()
    {
        $query = "SELECT * FROM group_provider_user_oauth WHERE provider_id=? AND user_id=?";
        $params = array(
            $this->_providerId,
            $this->_userId
        );
        $statement = $this->_connection->prepare($query);
        $statement->execute($params);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        $accessToken = new Zend_Oauth_Token_Access();
        $accessToken->setToken($row['oauth_token']);
        $accessToken->setTokenSecret($row['oauth_secret']);
        return $accessToken;
    }

    public function storeAccessToken(Zend_Oauth_Token_Access $accessToken)
    {
        $query = "INSERT INTO group_provider_user_oauth (provider_id, user_id, oauth_token, oauth_secret)
        VALUES(?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE oauth_token=VALUES(oauth_token), oauth_secret=VALUES(oauth_secret)";
        $params = array(
            $this->_providerId,
            $this->_userId,
            $accessToken->getToken(),
            $accessToken->getTokenSecret()
        );
        $statement = $this->_connection->prepare($query);
        $statement->execute($params);
        return true;
    }
}
