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
use OpenConext\EngineBlock\Service\ConsentHashServiceInterface;

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
     * @var ConsentHashServiceInterface
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
        bool $amPriorToConsentEnabled,
        bool $consentEnabled,
        ConsentHashServiceInterface $hashService
    ) {
        $this->_tableName = $tableName;
        $this->_mustStoreValues = $mustStoreValues;
        $this->_response = $response;
        $this->_responseAttributes = $responseAttributes;
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

    public function countTotalConsent(): int
    {
        $consentUid = $this->_getConsentUid();
        return $this->_hashService->countTotalConsent($consentUid);
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
        $parameters = array(
            sha1($this->_getConsentUid()),
            $serviceProvider->entityId,
            $this->_getStableAttributesHash($this->_responseAttributes),
            $consentType,
        );

        return $this->_hashService->storeConsentHash($parameters);
    }

    private function _hasStoredConsent(ServiceProvider $serviceProvider, $consentType): bool
    {
        $parameters = array(
            sha1($this->_getConsentUid()),
            $serviceProvider->entityId,
            $this->_getAttributesHash($this->_responseAttributes),
            $consentType,
        );

        $hasUnstableConsentHash = $this->_hashService->retrieveConsentHash($parameters);

        if ($hasUnstableConsentHash) {
            return true;
        }

        $parameters[2] = array(
            sha1($this->_getConsentUid()),
            $serviceProvider->entityId,
            $this->_getStableAttributesHash($this->_responseAttributes),
            $consentType,
        );

        return $this->_hashService->retrieveConsentHash($parameters);
    }
}
