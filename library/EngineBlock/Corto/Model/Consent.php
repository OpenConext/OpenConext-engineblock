<?php

class EngineBlock_Corto_Model_Consent
{
    private $_tableName;
    private $_mustStoreValues;
    private $_response;
    private $_responseAttributes;

    /** @var EngineBlock_Database_ConnectionFactory */
    private $_databaseConnectionFactory;

    /**
     * @param $tableName
     * @param $mustStoreValues
     * @param array $response
     * @param array $responseAttributes
     * @param EngineBlock_Database_ConnectionFactory $databaseConnectionFactory
     */
    public function __construct(
        $tableName,
        $mustStoreValues,
        array $response,
        array $responseAttributes,
        EngineBlock_Database_ConnectionFactory $databaseConnectionFactory
    )
    {
        $this->_tableName = $tableName;
        $this->_mustStoreValues = $mustStoreValues;
        $this->_response = $response;
        $this->_responseAttributes = $responseAttributes;
        $this->_databaseConnectionFactory = $databaseConnectionFactory;
    }

    /**
     * @return array
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function hasStoredConsent($serviceProviderEntityId)
    {
        try {
            $dbh = $this->_getConsentDatabaseConnection();
            if (!$dbh) {
                return false;
            }

            $attributesHash = $this->_getAttributesHash($this->_responseAttributes);

            $query = "SELECT * FROM {$this->_tableName} WHERE hashed_user_id = ? AND service_id = ? AND attribute = ?";
            $hashedUserId = sha1($this->_getConsentUid());
            $parameters = array(
                $hashedUserId,
                $serviceProviderEntityId,
                $attributesHash
            );

            /** @var $statement PDOStatement */
            $statement = $dbh->prepare($query);
            $statement->execute($parameters);
            $rows = $statement->fetchAll();

            if (count($rows) < 1) {
                // No stored consent found
                return false;
            }

            // Update usage date
            $statement = $dbh->prepare("UPDATE LOW_PRIORITY {$this->_tableName} SET usage_date = NOW() WHERE hashed_user_id = ? AND service_id = ?");
            $statement->execute(array(
                $hashedUserId,
                $serviceProviderEntityId,
             ));

            return true;
        } catch (PDOException $e) {
            throw new EngineBlock_Corto_ProxyServer_Exception(
                "Consent retrieval failed! Error: " . $e->getMessage(),
                EngineBlock_Exception::CODE_ALERT
            );
        }
    }

    public function storeConsent($serviceProviderEntityId)
    {
        $dbh = $this->_getConsentDatabaseConnection();
        if (!$dbh) {
            return false;
        }

        $query = "INSERT INTO consent (usage_date, hashed_user_id, service_id, attribute)
                  VALUES (NOW(), ?, ?, ?)
                  ON DUPLICATE KEY UPDATE usage_date=VALUES(usage_date), attribute=VALUES(attribute)";
        $parameters = array(
            sha1($this->_getConsentUid()),
            $serviceProviderEntityId,
            $this->_getAttributesHash($this->_responseAttributes)
        );

        $statement = $dbh->prepare($query);
        if (!$statement) {
            throw new EngineBlock_Exception(
                "Unable to create a prepared statement to insert consent?!",
                EngineBlock_Exception::CODE_CRITICAL
            );
        }

        /** @var $statement PDOStatement */
        if (!$statement->execute($parameters)) {
            throw new EngineBlock_Corto_Module_Services_Exception(
                "Error storing consent: " . var_export($statement->errorInfo(), true),
                EngineBlock_Exception::CODE_CRITICAL
            );
        }

        return true;
    }

    public function countTotalConsent()
    {
        $dbh = $this->_getConsentDatabaseConnection();
        $hashedUserId = sha1($this->_getConsentUid());
        $query = "SELECT COUNT(*) FROM consent where hashed_user_id = ?";
        $parameters = array($hashedUserId);
        $statement = $dbh->prepare($query);
        if (!$statement) {
            throw new EngineBlock_Exception(
                "Unable to create a prepared statement to count consent?!", EngineBlock_Exception::CODE_ALERT
            );
        }
        /** @var $statement PDOStatement */
        $statement->execute($parameters);
        return (int)$statement->fetchColumn();
    }

    /**
     * @return bool|PDO
     */
    protected function _getConsentDatabaseConnection()
    {
        // We only use the write connection because consent is 3 queries of which only 1 light select query.
        return $this->_databaseConnectionFactory->create(EngineBlock_Database_ConnectionFactory::MODE_WRITE);
    }

    protected function _getConsentUid()
    {
        return $this->_response['saml:Assertion']['saml:Subject']['saml:NameID']['__v'];
    }

    protected function _getAttributesHash($attributes)
    {
        $hashBase = NULL;
        if ($this->_mustStoreValues) {
            ksort($attributes);
            $hashBase = serialize($attributes);
        } else {
            $names = array_keys($attributes);
            sort($names);
            $hashBase = implode('|', $names);
        }
        return sha1($hashBase);
    }
}
