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

require_once(dirname(__FILE__) . '/../../../autoloading.inc.php');

require_once 'Osapi/Loader.php';

class Test_Osapi_Storage_DatabaseTest extends PHPUnit_Framework_TestCase
{
    const MOCK_DB_FILE_PATH = '/tmp/surfconext_eb_mock_osapi_storage.db';

    const FIXTURE_KEY = 'OAuth:SurfConextTest::urn:collab:person:surfguest.nl:relax';

    const FIXTURE_VALUE = 'abcdefg';

    const FIXTURE_UPDATED_TIME = '10000';

    /**
     * @var PDO
     */
    protected $_connection;

    /**
     * @var Osapi_Storage_Database
     */
    protected $_storage;

    public function testGet()
    {
        $this->assertEquals(
            self::FIXTURE_VALUE,
            $this->_storage->get(self::FIXTURE_KEY),
            'Getting a known key without expiration'
        );

        $this->assertEquals(
            self::FIXTURE_VALUE,
            $this->_storage->get(self::FIXTURE_KEY, 3600),
            'Getting a known key with expiration of 1 hour (should still be valid)'
        );

        $this->assertFalse(
            $this->_storage->get(self::FIXTURE_KEY, 1),
            "Storage returns false after expiration"
        );
    }

    public function testSet()
    {
        $newValue = 'newval';
        $this->_storage->set(self::FIXTURE_KEY, $newValue);
        $this->assertEquals(
            $newValue,
            $this->_storage->get(self::FIXTURE_KEY),
            "Updating a known key returns the new value"
        );

        $newKey = "blaat";
        $this->_storage->set($newKey, $newValue);
        $this->assertEquals(
            $newValue,
            $this->_storage->get($newKey),
            "Inserting a new key and retrieving it works"
        );
    }

    public function testDelete()
    {
        $newKey   = "blaat";
        $newValue = "blaater";
        $this->_storage->set($newKey, $newValue);

        $this->_storage->delete($newKey);
        $this->assertFalse($this->_storage->get($newKey));
    }

    public function setUp()
    {
        if (file_exists(self::MOCK_DB_FILE_PATH)) {
            unlink(self::MOCK_DB_FILE_PATH);
        }
        $connection = new PDO('sqlite:' . self::MOCK_DB_FILE_PATH);

        $this->_createMockTable($connection);
        $this->_setFixtures($connection);

        $this->_connection = $connection;
        $this->_storage = new Osapi_Storage_Database($this->_connection);
        $this->_storage->setCurrentTime(10005); // 5 seconds after fixture has been added
    }

    protected function _createMockTable(PDO $connection)
    {
        $query = "CREATE TABLE osapi_storage ( key TEXT, value TEXT, updated_time INT)";
        $statement = $connection->query($query);
        if (!$statement) {
            throw new EngineBlock_Exception("Unable to execute query: " . $query);
        }

        $statement->execute();
        if ($connection->errorCode() !== "00000") {
            throw new EngineBlock_Exception("Error executing statement: " . var_export($connection->errorInfo(), true));
        }
    }

    protected function _setFixtures(PDO $connection)
    {
        $key            = self::FIXTURE_KEY;
        $value          = self::FIXTURE_VALUE;
        $updatedTime    = self::FIXTURE_UPDATED_TIME;
        $query = "INSERT INTO osapi_storage (key, value, updated_time)
            VALUES('$key', '$value', $updatedTime)";

        $statement = $connection->query($query);
        if (!$statement) {
            throw new EngineBlock_Exception("Unable to execute query: " . $query);
        }

        $statement->execute();
        if ($connection->errorCode() !== "00000") {
            throw new EngineBlock_Exception("Error executing statement: " . var_export($connection->errorInfo(), true));
        }
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        unset($this->_connection);
        unlink(self::MOCK_DB_FILE_PATH);
    }
}