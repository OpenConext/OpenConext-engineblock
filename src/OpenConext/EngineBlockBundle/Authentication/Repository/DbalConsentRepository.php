<?php

namespace OpenConext\EngineBlockBundle\Authentication\Repository;

use DateTime;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\DBALException;
use OpenConext\EngineBlock\Authentication\Model\Consent;
use OpenConext\EngineBlock\Authentication\Repository\ConsentRepository;
use OpenConext\EngineBlock\Authentication\Value\ConsentType;
use OpenConext\EngineBlock\Exception\RuntimeException;
use PDO;

final class DbalConsentRepository implements ConsentRepository
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
     *
     * @return Consent[]
     *
     * @throws RuntimeException
     */
    public function findAllFor($userId)
    {
        $sql       = '
            SELECT
                service_id
            ,   consent_date
            ,   consent_type
            ,   attribute
            FROM
                consent
            WHERE
                hashed_user_id=:hashed_user_id
        ';

        try {
            $statement = $this->connection->executeQuery($sql, ['hashed_user_id' => sha1($userId)]);
            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (DBALException $exception) {
            throw new RuntimeException('Could not fetch user consents from the database', 0, $exception);
        }

        return array_map(
            function (array $row) use ($userId) {
                return new Consent(
                    $userId,
                    $row['service_id'],
                    new DateTime($row['consent_date']),
                    new ConsentType($row['consent_type']),
                    $row['attribute']
                );
            },
            $rows
        );
    }

    /**
     * @param string $userId
     *
     * @throws RuntimeException
     */
    public function deleteAllFor($userId)
    {
        $sql = 'DELETE FROM consent WHERE hashed_user_id = :hashed_user_id';

        try {
            $this->connection->executeQuery($sql, ['hashed_user_id' => sha1($userId)]);
        } catch (DBALException $exception) {
            throw new RuntimeException('Could not delete user consents from the database', 0, $exception);
        }
    }
}
