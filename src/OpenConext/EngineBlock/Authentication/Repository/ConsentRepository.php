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

namespace OpenConext\EngineBlock\Authentication\Repository;

use OpenConext\EngineBlock\Authentication\Model\Consent;
use OpenConext\EngineBlock\Authentication\Value\ConsentVersion;

interface ConsentRepository
{
    /**
     * @param string $userId
     *
     * @return Consent[]
     */
    public function findAllFor($userId);

    /**
     * @param string $userId
     *
     * @return Consent[]
     */
    public function deleteAllFor($userId);

    public function deleteOneFor(string $userId, string $serviceProviderEntityId): bool;

    /**
     * Test if the consent row is set with an attribute hash either stable or unstable
     */
    public function hasConsentHash(array $parameters): ConsentVersion;

    /**
     * By default stores the stable consent hash. The legacy consent hash is left.
     */
    public function storeConsentHash(array $parameters): bool;

    /**
     * When a deprecated unstable consent hash is encoutered, we upgrade it to the new format using this
     * update consent hash method.
     */
    public function updateConsentHash(array $parameters): bool;

    public function countTotalConsent($consentUid): int;
}
