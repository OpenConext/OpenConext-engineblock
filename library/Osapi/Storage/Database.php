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

class Osapi_Storage_Database extends osapiStorage
{
    const TABLE_NAME = "osapi_storage";

    protected $_connection;

    protected $_currentTime;

    public function __construct(PDO $connection)
    {
        $this->_connection = $connection;
    }

    public function get($key, $expiration = false)
    {
        $tableName = self::TABLE_NAME;
        $query = "SELECT value FROM $tableName WHERE key=?";
        $params = array($key);
        if ($expiration) {
            $time = (isset($this->_currentTime) ? $this->_currentTime : time());
            $query .= " AND (updated_time + $expiration) > $time";
        }
        $statement = $this->_connection->query($query);
        if (!$statement) {
            throw new EngineBlock_Exception(
                "Unable to get prepare statement in Osapi Database Storage, error: " . var_export($this->_connection->errorInfo(), true)
            );
        }
        $statement->execute($params);
        if ($this->_connection->errorCode() !== '00000') {
            throw new EngineBlock_Exception(
                "Unable to get value from Osapi Database Storage, error: " . var_export($statement->errorInfo(), true)
            );
        }
        return $statement->fetchColumn(0);
    }

    public function set($key, $value)
    {
        $tableName = self::TABLE_NAME;

        $query = "UPDATE $tableName SET key=?, value=?, updated_time=" . time();
        $statement = $this->_connection->prepare($query);
        if (!$statement) {
            throw new EngineBlock_Exception(
                "Unable to get prepare statement in Osapi Database Storage, error: " .
                var_export($this->_connection->errorInfo(), true)
            );
        }
        $executed = $statement->execute(
            array(
                $key,
                $value
            )
        );
        if ($executed) {
            return true;
        }

        // Update not possible, try inserting.
        // Note that MySQL has the convenient INSERT ON DUPLICATE UPDATE
        // But testing is done with SQLite, which does not have this.

        $statement = $this->_connection->prepare(
            "INSERT INTO $tableName (key, value, updated_time) VALUES (?, ?, ?)"
        );
        if (!$statement) {
            throw new EngineBlock_Exception(
                "Unable to get prepare statement in Osapi Database Storage, error: " .
                var_export($this->_connection->errorInfo(), true)
            );
        }
        $executed = $statement->execute(
            array(
                $key,
                $value,
                (isset($this->_currentTime) ? $this->_currentTime : time())
            )
        );
        if (!$executed) {
            throw new EngineBlock_Exception(
                "Unable to execute INSERT/UPDATE for Osapi Database Storage, error: " .
                var_export($this->_connection->errorInfo(), true)
            );
        }

        return true;
    }

    public function delete($key)
    {
        $tableName = self::TABLE_NAME;
        $statement = $this->_connection->prepare(
            "DELETE FROM $tableName WHERE key = ?"
        );
        $executed = $statement->execute(array($key));
        if (!$executed) {
            throw new EngineBlock_Exception(
                "Unable to delete key from Osapi Database Storage, error: " . $statement->errorCode()
            );
        }
        return true;
    }

    /**
     * For testing purposes
     *
     * @param  $time
     * @return Osapi_Storage_Database
     */
    public function setCurrentTime($time)
    {
        $this->_currentTime = $time;
        return $this;
    }
}