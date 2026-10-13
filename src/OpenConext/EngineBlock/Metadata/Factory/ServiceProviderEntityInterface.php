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

use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;

interface ServiceProviderEntityInterface
{

    public function getId(): ?int;

    public function getEntityId(): string;

    public function getName(string $locale): ?string;

    public function getDescription(string $locale): ?string;

    public function getDisplayName(string $locale): ?string;

    public function getLogo(): ?Logo;

    /**
     * The SAML2 metadata specification dictates to only display the OrganizationData when it is complete
     * (url, display name and name). This method verifies the organization data is complete
     */
    public function hasCompleteOrganizationData(string $locale): bool;

    /**
     * @param string $locale
     * @return Organization|null
     */
    public function getOrganization(string $locale): ?Organization;

    public function getKeywords(string $locale): string;

    /**
     * @return X509Certificate[]
     */
    public function getCertificates(): array;

    public function getWorkflowState(): string;

    /**
     * @return ContactPerson[]
     */
    public function getContactPersons(): array;
    public function getNameIdFormat(): ?string;

    /**
     * @return string[]
     */
    public function getSupportedNameIdFormats(): array;

    public function getSingleLogoutService(): ?Service;

    public function isRequestsMustBeSigned(): bool;

    public function getManipulation(): ?string;

    /**
     * @return Coins
     */
    public function getCoins(): Coins;

    /**
     * @return AttributeReleasePolicy|null
     */
    public function getAttributeReleasePolicy(): ?AttributeReleasePolicy;

    /**
     * @return IndexedService[]
     */
    public function getAssertionConsumerServices(): array;

    /**
     * @return string[]
     */
    public function getAllowedIdpEntityIds(): array;

    public function isAllowAll(): bool;

    /**
     * @return RequestedAttribute[]|null
     */
    public function getRequestedAttributes(): ?array;

    public function getSupportUrl(string $locale): ?string;

    public function isAllowed(string $idpEntityId): bool;

    public function isAttributeAggregationRequired(): bool;

    public function getMdui(): Mdui;
}
