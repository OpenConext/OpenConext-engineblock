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

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Authentication\Value\ConsentType;
use OpenConext\EngineBlock\Service\Consent\ConsentHashService;

class EngineBlock_Corto_Model_Consent
{
    /**
     * @var string
     */
    private $_tableName;

    /**
     * @var bool
     */
    private $_mustStoreValues;

    /**
     * @var EngineBlock_Saml2_ResponseAnnotationDecorator
     */
    private $_response;
    /**
     * @var array All attributes as an associative array.
     */
    private $_responseAttributes;

    /**
     * @var EngineBlock_Database_ConnectionFactory
     */
    private $_databaseConnectionFactory;

    /**
     * A reflection of the eb.run_all_manipulations_prior_to_consent feature flag
     *
     * @var bool
     */
    private $_amPriorToConsentEnabled;

    /**
     * A reflection of the eb.feature_enable_consent feature flag
     *
     * @var bool
     */
    private $_consentEnabled;

    /**
     * @var ConsentHashService
     */
    private $_hashService;

    /**
     * @param string $tableName
     * @param bool $mustStoreValues
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator $response
     * @param array $responseAttributes
     * @param EngineBlock_Database_ConnectionFactory $databaseConnectionFactory
     * @param bool $amPriorToConsentEnabled Is the run_all_manipulations_prior_to_consent feature enabled or not
     * @param bool $consentEnabled Is the feature_enable_consent feature enabled or not
     * @param ConsentHashService $hashService
     */
    public function __construct(
        $tableName,
        $mustStoreValues,
        EngineBlock_Saml2_ResponseAnnotationDecorator $response,
        array $responseAttributes,
        EngineBlock_Database_ConnectionFactory $databaseConnectionFactory,
        $amPriorToConsentEnabled,
        $consentEnabled,
        $hashService
    )
    {
        $this->_tableName = $tableName;
        $this->_mustStoreValues = $mustStoreValues;
        $this->_response = $response;
        $this->_responseAttributes = $responseAttributes;
        $this->_databaseConnectionFactory = $databaseConnectionFactory;
        $this->_amPriorToConsentEnabled = $amPriorToConsentEnabled;
        $this->_hashService = $hashService;
        $this->_consentEnabled = $consentEnabled;
    }

    public function explicitConsentWasGivenFor(ServiceProvider $serviceProvider): bool
    {
        return !$this->_consentEnabled ||
            $this->_hasStoredConsent($serviceProvider, ConsentType::TYPE_EXPLICIT);
    }

    public function implicitConsentWasGivenFor(ServiceProvider $serviceProvider): bool
    {
        return !$this->_consentEnabled ||
            $this->_hasStoredConsent($serviceProvider, ConsentType::TYPE_IMPLICIT);
    }

    public function giveExplicitConsentFor(ServiceProvider $serviceProvider): bool
    {
        return !$this->_consentEnabled ||
            $this->_storeConsent($serviceProvider, ConsentType::TYPE_EXPLICIT);
    }

    public function giveImplicitConsentFor(ServiceProvider $serviceProvider): bool
    {
        return !$this->_consentEnabled ||
            $this->_storeConsent($serviceProvider, ConsentType::TYPE_IMPLICIT);
    }

    /**
     * @throws EngineBlock_Exception
     */
    public function countTotalConsent(): int
    {
        $dbh = $this->_getConsentDatabaseConnection();
        $hashedUserId = sha1($this->_getConsentUid());
        $query = "SELECT COUNT(*) FROM consent where hashed_user_id = ?";
        $parameters = array($hashedUserId);
        $statement = $dbh->prepare($query);
        if (!$statement) {
            throw new EngineBlock_Exception(
                "Unable to create a prepared statement to count consent?!", EngineBlock_Exception::CODE_ALERT
            );
        }
        /** @var $statement PDOStatement */
        $statement->execute($parameters);
        return (int)$statement->fetchColumn();
    }

    /**
     * @return bool|PDO
     */
    protected function _getConsentDatabaseConnection()
    {
        return $this->_databaseConnectionFactory->create();
    }

    protected function _getConsentUid()
    {
        if ($this->_amPriorToConsentEnabled) {
            $nameIdValue = $this->_response->getOriginalResponse()->getCollabPersonId();
            return $nameIdValue;
        }
        return $this->_response->getNameIdValue();
    }

    protected function _getAttributesHash($attributes): string
    {
        return $this->_hashService->getUnstableAttributesHash($attributes, $this->_mustStoreValues);
    }

    protected function _getStableAttributesHash($attributes): string
    {
        return $this->_hashService->getStableAttributesHash($attributes, $this->_mustStoreValues);
    }

    private function _storeConsent(ServiceProvider $serviceProvider, $consentType): bool
    {
        $dbh = $this->_getConsentDatabaseConnection();
        if (!$dbh) {
            return false;
        }

        $query = "INSERT INTO consent (hashed_user_id, service_id, attribute, consent_type, consent_date, deleted_at)
                  VALUES (?, ?, ?, ?, NOW(), '0000-00-00 00:00:00')
                  ON DUPLICATE KEY UPDATE attribute=VALUES(attribute), consent_type=VALUES(consent_type), consent_date=NOW()";
        $parameters = array(
            sha1($this->_getConsentUid()),
            $serviceProvider->entityId,
            $this->_getStableAttributesHash($this->_responseAttributes),
            $consentType,
        );

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

    private function _hasStoredConsent(ServiceProvider $serviceProvider, $consentType): bool
    {
        $dbh = $this->_getConsentDatabaseConnection();
        if (!$dbh) {
            return false;
        }

        $unstableConsentHash = $this->_getAttributesHash($this->_responseAttributes);
        $hasUnstableConsentHash = $this->retrieveConsentHashFromDb($dbh, $serviceProvider, $consentType, $unstableConsentHash);

        if ($hasUnstableConsentHash) {
            return true;
        }

        $stableConsentHash = $this->_getStableAttributesHash($this->_responseAttributes);
        return $this->retrieveConsentHashFromDb($dbh, $serviceProvider, $consentType, $stableConsentHash);
    }

    private function retrieveConsentHashFromDb(PDO $dbh, ServiceProvider $serviceProvider, $consentType, $attributesHash): bool
    {
        try {
            $query = "
                SELECT *
                FROM {$this->_tableName}
                WHERE hashed_user_id = ?
                  AND service_id = ?
                  AND attribute = ?
                  AND consent_type = ?
                  AND deleted_at IS NULL
            ";
            $hashedUserId = sha1($this->_getConsentUid());
            $parameters = array(
                $hashedUserId,
                $serviceProvider->entityId,
                $attributesHash,
                $consentType,
            );

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
}
