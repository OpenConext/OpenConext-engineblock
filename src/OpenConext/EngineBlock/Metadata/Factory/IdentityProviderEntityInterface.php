<?php declare(strict_types=1);
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

namespace OpenConext\EngineBlock\Metadata\Factory;

use DateTime;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ConsentSettings;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;

interface IdentityProviderEntityInterface
{

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return string
     */
    public function getEntityId(): string;

    /**
     * @param $locale
     * @return string
     */
    public function getName($locale): string;

    /**
     * @param $locale
     * @return string
     */
    public function getDescription($locale): string;

    /**
     * @param $locale
     * @return string
     */
    public function getDisplayName($locale): string;

    /**
     * @return Logo|null
     */
    public function getLogo(): ?Logo;

    /**
     * The SAML2 metadata specification dictates to only display the OrganizationData when it is complete
     * (url, display name and name). This method verifies the organization data is complete
     */
    public function hasCompleteOrganizationData(string $locale): bool;

    /**
     * @param $locale
     * @return Organization|null
     */
    public function getOrganization($locale): ?Organization;

    /**
     * @param $locale
     * @return string
     */
    public function getKeywords($locale): string;

    /**
     * @return X509Certificate[]
     */
    public function getCertificates(): array;

    /**
     * @return string
     */
    public function getWorkflowState(): string;

    /**
     * @return ContactPerson[]
     */
    public function getContactPersons(): array;

    /**
     * @return string
     */
    public function getNameIdFormat(): string;

    /**
     * @return string[]
     */
    public function getSupportedNameIdFormats(): array;

    /**
     * @return Service|null
     */
    public function getSingleLogoutService(): ?Service;

    /**
     * @return bool
     */
    public function isRequestsMustBeSigned(): bool;

    /**
     * @return string
     */
    public function getManipulation(): string;

    /**
     * @return Coins
     */
    public function getCoins(): Coins;

    /**
     * @return bool
     */
    public function isEnabledInWayf(): bool;

    /**
     * @return Service[]
     */
    public function getSingleSignOnServices(): array;

    /**
     * @return ConsentSettings
     */
    public function getConsentSettings(): ConsentSettings;

    /**
     * @return ShibMdScope[]
     */
    public function getShibMdScopes(): array;

    public function getMdui(): Mdui;
}
