<?php

class EngineBlock_Database_ConnectionFactory
{
    public function __construct(\Doctrine\DBAL\Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Create a new Database connection, for a given mode self::MODE_READ and self::MODE_WRITE,
     * defaults to write mode.
     *
     * @return \Doctrine\DBAL\Driver\PDOConnection
     *
     * @deprecated This functionality will be removed
     */
    public function create()
    {
        return $this->connection->getWrappedConnection();
    }
}


