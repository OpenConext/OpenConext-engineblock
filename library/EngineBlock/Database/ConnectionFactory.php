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

class EngineBlock_Database_ConnectionFactory
{
    const MODE_READ     = 'r';
    const MODE_WRITE    = 'w';

    /**
     * Create a new Database connection, for a given mode self::MODE_READ and self::MODE_WRITE,
     * defaults to write mode.
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

        $databaseSettings = $this->_getDatabaseSettings();

        if      ($mode === self::MODE_READ) {
            try {
                return $this->_createReadConnection($databaseSettings);
            }
            catch (Exception $e) {
                $additionalInfo = new EngineBlock_Log_Message_AdditionalInfo(
                    null, null, null, $e->getTraceAsString()
                );
                EngineBlock_ApplicationSingleton::getLog()->err(
                    "Unable to create a Read connection, trying to create a write connection, exception: " . print_r($e, true),
                    $additionalInfo
                );
                return $this->_createWriteConnection($databaseSettings);
            }
        }
        else if ($mode === self::MODE_WRITE) {
            return $this->_createWriteConnection($databaseSettings);
        }
        else {
            throw new EngineBlock_Database_Exception("Requested database connection with unknown mode '$mode'");
        }
    }

    /**
     * @return PDO
     */
    protected function _createWriteConnection($databaseSettings)
    {
        if (!isset($databaseSettings->masters)) {
            throw new EngineBlock_Database_Exception('Unable to find any settings for a database we can write to (masters)');
        }

        return $this->_createServerConnection($databaseSettings->masters->toArray(), $databaseSettings);
    }

    /**
     * @return PDO
     */
    protected function _createReadConnection($databaseSettings)
    {
        if (!isset($databaseSettings->slaves)) {
            throw new EngineBlock_Database_Exception('Unable to find any settings for a database we can read from (slaves)');
        }

        return $this->_createServerConnection($databaseSettings->slaves->toArray(), $databaseSettings);
    }

    protected function _createServerConnection($servers, $databaseSettings)
    {
        $randomServerKey = array_rand($servers);
        $randomServerName = $servers[$randomServerKey];
        if (!isset($databaseSettings->$randomServerName)) {
            throw new EngineBlock_Database_Exception("Unable to use database.$randomServerName for connection?!");
        }
        $randomServerSettings = $databaseSettings->$randomServerName;

        if (!isset($randomServerSettings->dsn) || !isset($randomServerSettings->user) || !isset($randomServerSettings->password)) {
            throw new EngineBlock_Database_Exception('Database settings missing a Dsn, User or Password setting!');
        }

        $dbh = new PDO(
            $randomServerSettings->dsn,
            $randomServerSettings->user,
            $randomServerSettings->password,
            array(
                PDO::ATTR_PERSISTENT => isset($randomServerSettings->use_persistent) ?
                    (bool)$randomServerSettings->use_persistent :
                    true
            )
        );
        return $dbh;
    }

    protected function _getConfiguration()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
    }

    protected function _getDatabaseSettings()
    {
        $configuration = $this->_getConfiguration();
        if (!isset($configuration->database)) {
            throw new EngineBlock_Database_Exception("No database settings?!");
        }
        return $configuration->database;
    }
}


