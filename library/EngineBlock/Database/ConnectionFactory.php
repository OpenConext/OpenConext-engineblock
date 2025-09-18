<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;

class EngineBlock_Database_ConnectionFactory
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(EntityManager $em)
    {
        $this->connection = $em->getConnection();
    }

    /**
     * Create a new Database connection, for a given mode self::MODE_READ and self::MODE_WRITE,
     * defaults to write mode.
     *
     * @return \Doctrine\DBAL\Driver\PDO\Connection
     *
     * @deprecated This functionality will be removed
     */
    public function create()
    {
        return $this->connection->getWrappedConnection();
    }
}


