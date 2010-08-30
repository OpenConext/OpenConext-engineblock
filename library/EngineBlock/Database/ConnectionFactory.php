<?php
 
class EngineBlock_Database_ConnectionFactory
{
    const MODE_READ     = 'r';
    const MODE_WRITE    = 'w';

    /**
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

        if      ($mode === self::MODE_READ) {
            return self::_createReadConnection();
        }
        else if ($mode === self::MODE_WRITE) {
            return self::_createWriteConnection();
        }
        else {
            throw new EngineBlock_Exception("Requested database connection with unknown mode '$mode'");
        }
    }

    /**
     * @static
     * @return PDO
     */
    protected static function _createWriteConnection()
    {
        $writeSettings = EngineBlock_ApplicationSingleton::getInstance()->getConfigurationValuesForPrefix('Db.Write.');
        if (empty($writeSettings)) {
            throw new EngineBlock_Exception('Unable to find any settings for a database we can write to');
        }

        $writeSettings = self::_arrayizeConfigurationValues($writeSettings);
        if (!isset($writeSettings['Db']['Write']) || count($writeSettings['Db']['Write'])===0) {
            throw new EngineBlock_Exception('No database servers found to write to in configuration. (Db.Write.0 missing?)');
        }

        $writeServers = $writeSettings['Db']['Write'];
        $randomServerKey = array_rand($writeServers);
        $randomWriteServer = $writeServers[$randomServerKey];
        if (!isset($randomWriteServer['Dsn']) || !isset($randomWriteServer['User']) || !isset($randomWriteServer['Password'])) {
            throw new EngineBlock_Exception('Write settings missing a Dsn, User or Password setting!');
        }

        $dbh = new PDO(
            $randomWriteServer['Dsn'],
            $randomWriteServer['User'],
            $randomWriteServer['Password']
        );
        return $dbh;
    }

    /**
     * @static
     * @return PDO
     */
    protected static function _createReadConnection()
    {
        $readSettings = EngineBlock_ApplicationSingleton::getInstance()->getConfigurationValuesForPrefix('Db.Read.');
        if (empty($readSettings)) {
            throw new EngineBlock_Exception('Unable to find any settings for a database we can read to');
        }

        $readSettings = self::_arrayizeConfigurationValues($readSettings);
        if (!isset($readSettings['Db']['Read']) || count($readSettings['Db']['Read'])===0) {
            throw new EngineBlock_Exception('No database servers found to read to in configuration. (Db.Read.0 missing?)');
        }

        $randomReadSettings = array_rand($readSettings['Db']['Read']);
        if (!isset($randomReadSettings['Dsn']) || !isset($randomReadSettings['User']) || !isset($randomReadSettings['Password'])) {
            throw new EngineBlock_Exception('Read settings missing a Dsn, User or Password setting!');
        }

        $dbh = new PDO(
            $randomReadSettings['Dsn'],
            $randomReadSettings['User'],
            $randomReadSettings['Password']
        );
        return $dbh;
    }

    /**
     * Converts a configuration array with keys like Db.Write.0.Dsn to a multi-dimensional array.
     *
     * @example 'Db.Write.0.Dsn'=  'mysql:localhost' => array('Db'=>array('Write'=>array(0 => array('Dsn' => 'mysql:localhost'))))
     *
     * @static
     * @param  $configurationValues
     * @return array
     */
    protected static function _arrayizeConfigurationValues($configurationValues)
    {
        $configurationValuesArray = array();
        foreach ($configurationValues as $key => $value) {
            $exploded = explode('.', $key);
            $pointer = &$configurationValuesArray;
            while (count($exploded)>1) {
                $part = array_shift($exploded);
                if (is_numeric($part)) {
                    $part = (int)$part;
                }
                if (!isset($pointer[$part])) {
                    $pointer[$part] = array();
                }
                $pointer = &$pointer[$part];
            }
            $lastPart = array_shift($exploded);
            $pointer[$lastPart] = $value;
        }
        return $configurationValuesArray;
    }
}
