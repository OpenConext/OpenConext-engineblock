<?php
 
class EngineBlock_Database_ConnectionFactory
{
    const MODE_READ     = 'r';
    const MODE_WRITE    = 'w';

    /**
     * Create a new Database connection, for a given mode self::MODE_READ and self::MODE_WRITE, defaults to write mode.
     *
     * @static
     * @throws EngineBlock_Exception
     * @param  $mode
     * @return PDO
     */
    public function create($mode = null)
    {
        if ($mode === null) {
            $mode = self::MODE_WRITE;
        }

        $configuration = $this->_getConfiguration();
        if (!isset($configuration->database)) {
            throw new EngineBlock_Exception("No database settings?!");
        }
        $databaseSettings = $configuration->database;

        if      ($mode === self::MODE_READ) {
            return $this->_createReadConnection($databaseSettings);
        }
        else if ($mode === self::MODE_WRITE) {
            return $this->_createWriteConnection($databaseSettings);
        }
        else {
            throw new EngineBlock_Exception("Requested database connection with unknown mode '$mode'");
        }
    }

    /**
     * @return PDO
     */
    protected function _createWriteConnection($databaseSettings)
    {
        if (!isset($databaseSettings->masters)) {
            throw new EngineBlock_Exception('Unable to find any settings for a database we can write to (masters)');
        }

        return $this->_createServerConnection($databaseSettings->masters->toArray(), $databaseSettings);
    }

    /**
     * @return PDO
     */
    protected function _createReadConnection($databaseSettings)
    {
        if (!isset($databaseSettings->slaves)) {
            throw new EngineBlock_Exception('Unable to find any settings for a database we can read from (slaves)');
        }

        return $this->_createServerConnection($databaseSettings->slaves->toArray(), $databaseSettings);
    }

    protected function _createServerConnection($servers, $databaseSettings)
    {
        $randomServerKey = array_rand($servers);
        $randomServerName = $servers[$randomServerKey];
        if (!isset($databaseSettings->$randomServerName)) {
            throw new EngineBlock_Exception("Unable to use database.$randomServerName for connection?!");
        }
        $randomServerSettings = $databaseSettings->$randomServerName;

        if (!isset($randomServerSettings->dsn) || !isset($randomServerSettings->user) || !isset($randomServerSettings->password)) {
            throw new EngineBlock_Exception('Database settings missing a Dsn, User or Password setting!');
        }

        $dbh = new PDO(
            $randomServerSettings->dsn,
            $randomServerSettings->user,
            $randomServerSettings->password
        );
        return $dbh;
    }

    protected function _getConfiguration()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
    }
}
