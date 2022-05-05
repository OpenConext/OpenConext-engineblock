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

use OpenConext\EngineBlock\Authentication\Value\ConsentVersion;

interface ConsentHashServiceInterface
{
    /**
     * Retrieve the consent hash
     */
    public function retrieveConsentHash(array $parameters): ConsentVersion;

    public function storeConsentHash(array $parameters): bool;

    public function updateConsentHash(array $parameters): bool;

    public function countTotalConsent($consentUid): int;

    public function getUnstableAttributesHash(array $attributes, bool $mustStoreValues): string;

    public function getStableAttributesHash(array $attributes, bool $mustStoreValues) : string;
}
