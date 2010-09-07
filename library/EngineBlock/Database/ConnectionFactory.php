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
    public static function create($mode = null)
    {
        if ($mode === null) {
            $mode = self::MODE_WRITE;
        }

        $configuration = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
        if (!isset($configuration->database)) {
            throw new EngineBlock_Exception("No database settings?!");
        }
        $databaseSettings = $configuration->database;

        if      ($mode === self::MODE_READ) {
            return self::_createReadConnection($databaseSettings);
        }
        else if ($mode === self::MODE_WRITE) {
            return self::_createWriteConnection($databaseSettings);
        }
        else {
            throw new EngineBlock_Exception("Requested database connection with unknown mode '$mode'");
        }
    }

    /**
     * @static
     * @return PDO
     */
    protected static function _createWriteConnection($databaseSettings)
    {
        if (!isset($databaseSettings->masters)) {
            throw new EngineBlock_Exception('Unable to find any settings for a database we can write to (masters)');
        }

        return self::_createServerConnection($databaseSettings->masters->toArray(), $databaseSettings);
    }

    /**
     * @static
     * @return PDO
     */
    protected static function _createReadConnection($databaseSettings)
    {
        if (!isset($databaseSettings->slaves)) {
            throw new EngineBlock_Exception('Unable to find any settings for a database we can read from (slaves)');
        }

        return self::_createServerConnection($databaseSettings->slaves->toArray(), $databaseSettings);
    }

    protected static function _createServerConnection($servers, $databaseSettings)
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
}
