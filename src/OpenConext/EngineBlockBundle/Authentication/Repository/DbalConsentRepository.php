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

namespace OpenConext\EngineBlockBundle\Authentication\Repository;

use DateTime;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\DBALException;
use OpenConext\EngineBlock\Authentication\Model\Consent;
use OpenConext\EngineBlock\Authentication\Repository\ConsentRepository;
use OpenConext\EngineBlock\Authentication\Value\ConsentType;
use OpenConext\EngineBlock\Exception\RuntimeException;
use PDO;
use function sha1;

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

    /**
     * @param string $userId
     *
     * @throws RuntimeException
     */
    public function deleteOneFor(string $userId, string $serviceProviderEntityId): bool
    {
        $sql = 'DELETE FROM consent WHERE hashed_user_id = :hashed_user_id AND service_id = :service_id ';

        try {
            $result = $this->connection->executeQuery(
                $sql,
                [
                    'hashed_user_id' => sha1($userId),
                    'service_id' => $serviceProviderEntityId
                ]
            );
            return $result->rowCount() > 0;
        } catch (DBALException $exception) {
            throw new RuntimeException(
                'Could not delete user consent from the database for a specific SP',
                0,
                $exception
            );
        }
    }
}
