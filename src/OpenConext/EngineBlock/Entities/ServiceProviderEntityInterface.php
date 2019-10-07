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

namespace OpenConext\EngineBlock\Entities;

use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;

interface ServiceProviderEntityInterface
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
     * @return string
     */
    public function getNameNl(): string;

    /**
     * @return string
     */
    public function getNameEn(): string;

    /**
     * @return string
     */
    public function getDescriptionNl(): string;

    /**
     * @return string
     */
    public function getDescriptionEn(): string;

    /**
     * @return string
     */
    public function getDisplayNameNl(): string;

    /**
     * @return string
     */
    public function getDisplayNameEn(): string;

    /**
     * @return Logo
     */
    public function getLogo(): Logo;

    /**
     * @return Organization
     */
    public function getOrganizationNl(): Organization;

    /**
     * @return Organization
     */
    public function getOrganizationEn(): Organization;

    /**
     * @return string
     */
    public function getKeywordsNl(): string;

    /**
     * @return string
     */
    public function getKeywordsEn(): string;

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
     * @return Service
     */
    public function getSingleLogoutService(): Service;

    /**
     * @return bool
     */
    public function isRequestsMustBeSigned(): bool;

    /**
     * @return string
     */
    public function getResponseProcessingService(): string;

    /**
     * @return string
     */
    public function getManipulation(): string;

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

    /**
     * @return bool
     */
    public function isAllowAll(): bool;

    /**
     * @return RequestedAttribute[]|null
     */
    public function getRequestedAttributes(): ?array;

    /**
     * @return string|null
     */
    public function getSupportUrlEn(): ?string;

    /**
     * @return string|null
     */
    public function getSupportUrlNl(): ?string;
}
