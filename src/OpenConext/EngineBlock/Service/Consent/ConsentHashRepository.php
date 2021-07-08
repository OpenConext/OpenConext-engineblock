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

namespace OpenConext\EngineBlock\Service\Consent;

use EngineBlock_Corto_Module_Services_Exception;
use EngineBlock_Corto_ProxyServer_Exception;
use EngineBlock_Exception;
use PDO;
use PDOException;
use PDOStatement;

class ConsentHashRepository
{
    /**
     * @throws EngineBlock_Corto_ProxyServer_Exception
     */
    public function retrieveConsentHashFromDb(PDO $dbh, array $parameters): bool
    {
        try {
            $query = "SELECT * FROM {$this->_tableName} WHERE hashed_user_id = ? AND service_id = ? AND attribute = ? AND consent_type = ?";

            /** @var $statement PDOStatement */
            $statement = $dbh->prepare($query);
            $statement->execute($parameters);
            $rows = $statement->fetchAll();

            if (count($rows) < 1) {
                // No stored consent found
                return false;
            }

            return true;
        } catch (PDOException $e) {
            throw new EngineBlock_Corto_ProxyServer_Exception(
                sprintf('Consent retrieval failed! Error: "%s"', $e->getMessage()),
                EngineBlock_Exception::CODE_ALERT
            );
        }
    }

    /**
     * @throws EngineBlock_Corto_Module_Services_Exception
     * @throws EngineBlock_Exception
     */
    public function storeConsentHashInDb(PDO $dbh, array $parameters): bool
    {
        $query = "INSERT INTO consent (hashed_user_id, service_id, attribute, consent_type, consent_date)
                  VALUES (?, ?, ?, ?, NOW())
                  ON DUPLICATE KEY UPDATE attribute=VALUES(attribute), consent_type=VALUES(consent_type), consent_date=NOW()";

        $statement = $dbh->prepare($query);
        if (!$statement) {
            throw new EngineBlock_Exception(
                "Unable to create a prepared statement to insert consent?!",
                EngineBlock_Exception::CODE_CRITICAL
            );
        }

        /** @var $statement PDOStatement */
        if (!$statement->execute($parameters)) {
            throw new EngineBlock_Corto_Module_Services_Exception(
                sprintf('Error storing consent: "%s"', var_export($statement->errorInfo(), true)),
                EngineBlock_Exception::CODE_CRITICAL
            );
        }

        return true;
    }

    /**
     * @throws EngineBlock_Exception
     */
    public function countTotalConsent(PDO $dbh, $consentUid): int
    {
        $query = "SELECT COUNT(*) FROM consent where hashed_user_id = ?";
        $parameters = array(sha1($consentUid));
        $statement = $dbh->prepare($query);
        if (!$statement) {
            throw new EngineBlock_Exception(
                "Unable to create a prepared statement to count consent?!",
                EngineBlock_Exception::CODE_ALERT
            );
        }
        /** @var $statement PDOStatement */
        $statement->execute($parameters);
        return (int)$statement->fetchColumn();
    }
}
