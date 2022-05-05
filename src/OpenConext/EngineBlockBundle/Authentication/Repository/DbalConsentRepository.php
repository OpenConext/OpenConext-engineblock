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
use OpenConext\EngineBlock\Authentication\Value\ConsentVersion;
use OpenConext\EngineBlock\Exception\RuntimeException;
use PDO;
use PDOException;
use Psr\Log\LoggerInterface;
use function sha1;

final class DbalConsentRepository implements ConsentRepository
{
    /**
     * @var DbalConnection
     */
    private $connection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(DbalConnection $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
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
        $sql = '
            SELECT
                service_id
            ,   consent_date
            ,   consent_type
            ,   attribute
            FROM
                consent
            WHERE
                hashed_user_id=:hashed_user_id
            AND
                deleted_at IS NULL
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
            $this->logger->notice(sprintf('Removed consent for hashed user id %s (%s)', sha1($userId), $userId));
        } catch (DBALException $exception) {
            throw new RuntimeException(
                sprintf(
                    'Could not delete user consents from the database for user %s',
                    $userId
                ),
                0,
                $exception
            );
        }
    }

    /**
     * @throws RuntimeException
     */
    public function deleteOneFor(string $userId, string $serviceProviderEntityId): bool
    {
        $sql = '
            UPDATE
                consent
            SET
                deleted_at = NOW()
            WHERE
                hashed_user_id = :hashed_user_id
            AND
                service_id = :service_id
            AND
                deleted_at IS NULL
        ';
        try {
            $result = $this->connection->executeQuery(
                $sql,
                [
                    'hashed_user_id' => sha1($userId),
                    'service_id' => $serviceProviderEntityId
                ]
            );
            $this->logger->info(
                sprintf(
                    'Removed (soft delete) consent for hashed user id %s (%s), for service %s',
                    sha1($userId),
                    $userId,
                    $serviceProviderEntityId
                )
            );

            return $result->rowCount() > 0;
        } catch (DBALException $exception) {
            throw new RuntimeException(
                sprintf(
                    'Could not delete user %s consent from the database for a specific SP %s',
                    $userId,
                    $serviceProviderEntityId
                ),
                0,
                $exception
            );
        }
    }

    /**
     * @throws RuntimeException
     */
    public function hasConsentHash(array $parameters): ConsentVersion
    {
        try {
            $query = " SELECT
                            *
                        FROM
                            consent
                        WHERE
                            hashed_user_id = ?
                        AND
                            service_id = ?
                        AND
                            (attribute = ? OR attribute_stable = ?)
                        AND
                            consent_type = ?
                        AND
                            deleted_at IS NULL
            ";

            $statement = $this->connection->prepare($query);
            $statement->execute($parameters);
            $rows = $statement->fetchAll();

            if (count($rows) < 1) {
                // No stored consent found
                return ConsentVersion::notGiven();
            }

            if ($rows[0]['attribute_stable'] !== '') {
                return ConsentVersion::stable();
            }
            return ConsentVersion::unstable();
        } catch (PDOException $e) {
            throw new RuntimeException(sprintf('Consent retrieval failed! Error: "%s"', $e->getMessage()));
        }
    }

    /**
     * @throws RuntimeException
     */
    public function storeConsentHash(array $parameters): bool
    {
        $query = "INSERT INTO consent (hashed_user_id, service_id, attribute_stable, consent_type, consent_date, deleted_at)
                  VALUES (?, ?, ?, ?, NOW(), '0000-00-00 00:00:00')
                  ON DUPLICATE KEY UPDATE attribute_stable=VALUES(attribute_stable), consent_type=VALUES(consent_type), consent_date=NOW()";
        $statement = $this->connection->prepare($query);
        if (!$statement) {
            throw new RuntimeException("Unable to create a prepared statement to insert consent?!");
        }

        if (!$statement->execute($parameters)) {
            throw new RuntimeException(
                sprintf('Error storing consent: "%s"', var_export($statement->errorInfo(), true))
            );
        }

        return true;
    }

    /**
     * @throws RuntimeException
     */
    public function updateConsentHash(array $parameters): bool
    {
        $query = "
                UPDATE
                    consent
                SET
                    attribute_stable = ?
                WHERE
                    attribute = ?
                AND
                    hashed_user_id = ?
                AND
                    service_id = ?
                AND
                    consent_type = ?
                AND
                    deleted_at IS NULL
        ";
        $statement = $this->connection->prepare($query);
        if (!$statement) {
            throw new RuntimeException("Unable to create a prepared statement to update consent?!");
        }

        if (!$statement->execute($parameters)) {
            throw new RuntimeException(
                sprintf('Error storing updated consent: "%s"', var_export($statement->errorInfo(), true))
            );
        }

        return true;
    }

    /**
     * @throws RuntimeException
     */
    public function countTotalConsent($consentUid): int
    {
        $query = "SELECT COUNT(*) FROM consent where hashed_user_id = ? AND deleted_at IS NULL";
        $parameters = array(sha1($consentUid));
        $statement = $this->connection->prepare($query);
        if (!$statement) {
            throw new RuntimeException("Unable to create a prepared statement to count consent?!");
        }
        $statement->execute($parameters);
        return (int)$statement->fetchColumn();
    }
}
