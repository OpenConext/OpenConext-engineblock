<?php

namespace OpenConext\EngineBlock\Authentication\Repository;

use DateTime;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\DBALException;
use OpenConext\EngineBlock\Authentication\Model\Consent;
use OpenConext\EngineBlock\Authentication\Value\ConsentType;
use PDO;

final class ConsentRepository
{
    /**
     * @var DbalConnection
     */
    private $connection;

    /**
     * @param DbalConnection $connection
     */
    public function __construct(DbalConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $userId
     * @return Consent[]
     * @throws DBALException
     */
    public function findAllFor($userId)
    {
        $sql       = '
            SELECT
                service_id
            ,   consent_date
            ,   consent_type
            FROM
                consent
            WHERE
                hashed_user_id=:hashed_user_id
        ';

        $statement = $this->connection->executeQuery($sql, ['hashed_user_id' => sha1($userId)]);
        $rows      = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            function (array $row) use ($userId) {
                return new Consent(
                    $userId,
                    $row['service_id'],
                    new DateTime($row['consent_date']),
                    new ConsentType($row['consent_type'])
                );
            },
            $rows
        );
    }
}
