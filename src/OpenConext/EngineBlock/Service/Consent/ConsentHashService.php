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

use OpenConext\EngineBlock\Authentication\Repository\ConsentRepository;
use OpenConext\EngineBlock\Authentication\Value\ConsentHashQuery;
use OpenConext\EngineBlock\Authentication\Value\ConsentStoreParameters;
use OpenConext\EngineBlock\Authentication\Value\ConsentUpdateParameters;
use OpenConext\EngineBlock\Authentication\Value\ConsentVersion;
use OpenConext\EngineBlockBundle\Configuration\FeatureConfigurationInterface;
use function array_keys;
use function implode;
use function ksort;
use function sha1;
use function sort;

final class ConsentHashService implements ConsentHashServiceInterface
{
    /** @deprecated Remove after stable consent hash is running in production */
    private const FEATURE_MIGRATION = 'eb.stable_consent_hash_migration';

    /**
     * @var ConsentRepository
     */
    private $consentRepository;

    /**
     * @var FeatureConfigurationInterface
     */
    private $featureConfiguration;

    public function __construct(ConsentRepository $consentHashRepository, FeatureConfigurationInterface $featureConfiguration)
    {
        $this->consentRepository = $consentHashRepository;
        $this->featureConfiguration = $featureConfiguration;
    }

    public function retrieveConsentHash(ConsentHashQuery $query): ConsentVersion
    {
        return $this->consentRepository->hasConsentHash($query);
    }

    public function storeConsentHash(ConsentStoreParameters $parameters): bool
    {
        $migrationEnabled = $this->featureConfiguration->isEnabled(self::FEATURE_MIGRATION);

        if ($migrationEnabled) {
            $parameters = new ConsentStoreParameters(
                hashedUserId: $parameters->hashedUserId,
                serviceId: $parameters->serviceId,
                attributeStableHash: $parameters->attributeStableHash,
                consentType: $parameters->consentType,
                attributeHash: null,
            );
        }

        return $this->consentRepository->storeConsentHash($parameters);
    }

    public function updateConsentHash(ConsentUpdateParameters $parameters): bool
    {
        $migrationEnabled = $this->featureConfiguration->isEnabled(self::FEATURE_MIGRATION);

        if ($migrationEnabled) {
            $parameters = new ConsentUpdateParameters(
                attributeStableHash: $parameters->attributeStableHash,
                attributeHash: $parameters->attributeHash,
                hashedUserId: $parameters->hashedUserId,
                serviceId: $parameters->serviceId,
                consentType: $parameters->consentType,
                clearLegacyHash: true,
            );
        }

        return $this->consentRepository->updateConsentHash($parameters);
    }

    public function countTotalConsent(string $consentUid): int
    {
        return $this->consentRepository->countTotalConsent($consentUid);
    }

    public function getStableConsentHash(ConsentAttributes $attributes): string
    {
        return sha1($attributes->getCompareValue());
    }

    /**
     * @deprecated Remove after stable consent hash is running in production
     *
     * The old way of calculating the attribute hash, this is not stable as a change of the attribute order,
     * change of case, stray/empty attributes, and renumbered indexes can cause the hash to change. Leaving the
     * user to give consent once again for a service she previously gave consent for.
     */
    public function getUnstableAttributesHash(array $attributes, bool $mustStoreValues): string
    {
        if ($mustStoreValues) {
            ksort($attributes);
            $hashBase = serialize($attributes);
        } else {
            $names = array_keys($attributes);
            sort($names);
            $hashBase = implode('|', $names);
        }
        return sha1($hashBase);
    }
}
