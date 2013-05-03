<?php
class EngineBlock_Corto_Model_Consent_Repository
{
    /**
     * @var string
     */
    private $_tableName;

    /** @var EngineBlock_Database_ConnectionFactory */
    private $_databaseConnectionFactory;

    /**
     * @param EngineBlock_Database_ConnectionFactory $databaseConnectionFactory
     */
    public function __construct(
        EngineBlock_Database_ConnectionFactory $databaseConnectionFactory
    )
    {
        $this->_databaseConnectionFactory = $databaseConnectionFactory;
        // Note that this table name used to be loaded from EngineBlock_Corto_ProxyServer::getConfig('ConsentDbTable', 'consent'),
        // Since it the configuration did not seem to exist and since it was also used hardcoded, using config is removed
        $this->_tableName = 'consent';
    }

    /**
     * Returns true if consent is given
     *
     * @param EngineBlock_Corto_Model_Consent $consent
     * @throws EngineBlock_Corto_ProxyServer_Exception
     * @throws EngineBlock_Exception
     * @return bool
     */
    public function isStored(EngineBlock_Corto_Model_Consent $consent)
    {
        try {
            $dbh = $this->_getConsentDatabaseConnection();

            $query = "
                SELECT  consent_date
                FROM    {$this->_tableName}
                WHERE   hashed_user_id = ?
                    AND service_id = ?
                    AND attribute = ?";
            $parameters = array(
                $consent->getUserIdHash(),
                $consent->getServiceProviderEntityId(),
                $consent->getAttributesHash()
            );

            /** @var $statement PDOStatement */
            $statement = $dbh->prepare($query);
            $statement->execute($parameters);
            $rows = $statement->fetchAll();

            if (count($rows) < 1) {
                // No stored consent found
                return false;
            }

            return true;
        } catch (PDOException $e) {
            throw new EngineBlock_Corto_ProxyServer_Exception(
                "Consent retrieval failed! Error: " . $e->getMessage(),
                EngineBlock_Exception::CODE_ALERT
            );
        }
    }

    /**
     * @param EngineBlock_Corto_Model_Consent $consent
     */
    public function updateUsage(EngineBlock_Corto_Model_Consent $consent)
    {
        $consent->setUsageDate(new DateTime());
        $this->store($consent);
    }

    /**
     * @param EngineBlock_Corto_Model_Consent $consent
     * @throws EngineBlock_Exception
     * @internal param $usageDate
     */
    public  function store(EngineBlock_Corto_Model_Consent $consent)
    {
        $dbh = $this->_getConsentDatabaseConnection();

        $statement = $dbh->prepare("
          INSERT INTO {$this->_tableName} (
            usage_date,
            hashed_user_id,
            service_id,
            attribute
          ) VALUES (?, ?, ?, ?)
          ON DUPLICATE KEY UPDATE
            usage_date=VALUES(usage_date),
            attribute=VALUES(attribute
          )
        ");
        if (!$statement) {
            throw new EngineBlock_Exception(
                "Unable to create a prepared statement to insert consent?!",
                EngineBlock_Exception::CODE_CRITICAL
            );
        }

        /** @var $statement PDOStatement */
        if (!$statement->execute(array(
            $consent->getUsageDate()->format(DATE_ISO8601),
            $consent->getUserIdHash(),
            $consent->getServiceProviderEntityId(),
            $consent->getAttributesHash()
        ))) {
            throw new EngineBlock_Exception(
                "Error storing consent: " . var_export($statement->errorInfo(), true),
                EngineBlock_Exception::CODE_CRITICAL
            );
        }
    }

    public function countTotalConsent($hashedUserId)
    {
        $dbh = $this->_getConsentDatabaseConnection();
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
     * @throws EngineBlock_Exception
     * @return PDO
     */
    protected function _getConsentDatabaseConnection()
    {
        // We only use the write connection because consent is 3 queries of which only 1 light select query.
        $connection = $this->_databaseConnectionFactory->create(EngineBlock_Database_ConnectionFactory::MODE_WRITE);
        if(!$connection) {
            throw new EngineBlock_Exception('Could not get database connection');
        }

        return $connection;
    }
}