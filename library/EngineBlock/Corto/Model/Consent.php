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
     * @var ConsentHashService
     */
    private $_hashService;

    /**
     * @param bool $amPriorToConsentEnabled Is the run_all_manipulations_prior_to_consent feature enabled or not
     */
    public function __construct(
        string $tableName,
        bool $mustStoreValues,
        EngineBlock_Saml2_ResponseAnnotationDecorator $response,
        array $responseAttributes,
        EngineBlock_Database_ConnectionFactory $databaseConnectionFactory,
        bool $amPriorToConsentEnabled,
        ConsentHashService $hashService
    )
    {
        $this->_tableName = $tableName;
        $this->_mustStoreValues = $mustStoreValues;
        $this->_response = $response;
        $this->_responseAttributes = $responseAttributes;
        $this->_databaseConnectionFactory = $databaseConnectionFactory;
        $this->_amPriorToConsentEnabled = $amPriorToConsentEnabled;
        $this->_hashService = $hashService;
    }

    public function explicitConsentWasGivenFor(ServiceProvider $serviceProvider): bool
    {
        return $this->_hasStoredConsent($serviceProvider, ConsentType::TYPE_EXPLICIT);
    }

    public function implicitConsentWasGivenFor(ServiceProvider $serviceProvider): bool
    {
        return $this->_hasStoredConsent($serviceProvider, ConsentType::TYPE_IMPLICIT);
    }

    public function giveExplicitConsentFor(ServiceProvider $serviceProvider): bool
    {
        return $this->_storeConsent($serviceProvider, ConsentType::TYPE_EXPLICIT);
    }

    public function giveImplicitConsentFor(ServiceProvider $serviceProvider): bool
    {
        return $this->_storeConsent($serviceProvider, ConsentType::TYPE_IMPLICIT);
    }

    public function countTotalConsent(): int
    {
        $dbh = $this->_getConsentDatabaseConnection();
        if (!$dbh) {
            return 0;
        }

        $consentUid = $this->_getConsentUid();
        return $this->_hashService->countTotalConsent($dbh, $consentUid);
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

        $parameters = array(
            sha1($this->_getConsentUid()),
            $serviceProvider->entityId,
            $this->_getStableAttributesHash($this->_responseAttributes),
            $consentType,
        );

        return $this->_hashService->storeConsentHashInDb($dbh, $parameters);
    }

    private function _hasStoredConsent(ServiceProvider $serviceProvider, $consentType): bool
    {
        $dbh = $this->_getConsentDatabaseConnection();
        if (!$dbh) {
            return false;
        }

        $parameters = array(
            sha1($this->_getConsentUid()),
            $serviceProvider->entityId,
            $this->_getAttributesHash($this->_responseAttributes),
            $consentType,
        );

        $hasUnstableConsentHash = $this->_hashService->retrieveConsentHashFromDb($dbh, $parameters);

        if ($hasUnstableConsentHash) {
            return true;
        }

        $parameters[2] = array(
            sha1($this->_getConsentUid()),
            $serviceProvider->entityId,
            $this->_getStableAttributesHash($this->_responseAttributes),
            $consentType,
        );

        return $this->_hashService->retrieveConsentHashFromDb($dbh, $parameters);
    }
}
