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

namespace OpenConext\EngineBlock\Metadata\Factory\Adapter;

use OpenConext\EngineBlock\Metadata\Factory\ServiceProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;

/**
 * This ServiceProviderEntity is an immutable counterpart of the ServiceProvider Doctrine ORM Entity.
 *
 * This adapter is used to support the ORM entity by encapsulating it. This will make it easier to replace the ORM
 * entity ultimately.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ServiceProviderEntity implements ServiceProviderEntityInterface
{
    /**
     * @var ServiceProvider
     */
    private $entity;

    public function __construct(ServiceProvider $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->entity->id;
    }

    /**
     * @return string
     */
    public function getEntityId(): string
    {
        return $this->entity->entityId;
    }

    /**
     * @param $locale
     * @return string
     */
    public function getName($locale): string
    {
        switch (true) {
            case ($locale == 'nl'):
                return $this->entity->nameNl;
            case ($locale == 'en'):
                return $this->entity->nameEn;
            case ($locale == 'pt'):
                return $this->entity->namePt;
        }

        return '';
    }

    /**
     * @param $locale
     * @return string
     */
    public function getDescription($locale): string
    {
        if ($this->entity->getMdui()->hasDescription($locale)) {
            return $this->entity->getMdui()->getDescription($locale);
        }

        return '';
    }

    public function getDisplayName(string $locale): string
    {
        if ($this->entity->getMdui()->hasDisplayName($locale)) {
            return $this->entity->getMdui()->getDisplayName($locale);
        }

        return '';
    }

    /**
     * @return Logo
     */
    public function getLogo(): ?Logo
    {
        return $this->entity->getMdui()->getLogoOrNull();
    }

    public function hasCompleteOrganizationData(string $locale): bool
    {
        $organization = $this->entity->getOrganization($locale);
        if ($organization
            && is_string($organization->displayName) && $organization->displayName !== ''
            && is_string($organization->name) && $organization->name !== ''
            && is_string($organization->url) && $organization->url !== ''
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param $locale
     * @return Organization
     */
    public function getOrganization($locale): ?Organization
    {
        switch (true) {
            case ($locale == 'nl'):
                return $this->entity->organizationNl;
            case ($locale == 'en'):
                return $this->entity->organizationEn;
            case ($locale == 'pt'):
                return $this->entity->organizationPt;
        }

        return null;
    }

    /**
     * @param $locale
     * @return string
     */
    public function getKeywords($locale): string
    {
        if ($this->entity->getMdui()->hasKeywords($locale)) {
            return $this->entity->getMdui()->getKeywords($locale);
        }

        return '';
    }

    /**
     * @return X509Certificate[]
     */
    public function getCertificates(): array
    {
        return $this->entity->certificates;
    }

    /**
     * @return string
     */
    public function getWorkflowState(): string
    {
        return $this->entity->workflowState;
    }

    /**
     * @return ContactPerson[]
     */
    public function getContactPersons(): array
    {
        return $this->entity->contactPersons;
    }

    /**
     * @return null|string
     */
    public function getNameIdFormat(): ?string
    {
        return $this->entity->nameIdFormat;
    }

    /**
     * @return string[]
     */
    public function getSupportedNameIdFormats(): array
    {
        return $this->entity->supportedNameIdFormats;
    }

    /**
     * @return null|Service
     */
    public function getSingleLogoutService(): ?Service
    {
        return $this->entity->singleLogoutService;
    }

    /**
     * @return bool
     */
    public function isRequestsMustBeSigned(): bool
    {
        return $this->entity->requestsMustBeSigned;
    }

    /**
     * @return string
     */
    public function getManipulation(): string
    {
        return $this->entity->manipulation;
    }

    /**
     * @return Coins
     */
    public function getCoins(): Coins
    {
        return $this->entity->getCoins();
    }

    public function getMdui(): Mdui
    {
        return $this->entity->getMdui();
    }

    /**
     * @return AttributeReleasePolicy|null
     */
    public function getAttributeReleasePolicy(): ?AttributeReleasePolicy
    {
        return $this->entity->attributeReleasePolicy;
    }

    /**
     * @return IndexedService[]
     */
    public function getAssertionConsumerServices(): array
    {
        return $this->entity->assertionConsumerServices;
    }

    /**
     * @return string[]
     */
    public function getAllowedIdpEntityIds(): array
    {
        return $this->entity->allowedIdpEntityIds;
    }

    /**
     * @return bool
     */
    public function isAllowAll(): bool
    {
        return $this->entity->allowAll;
    }

    /**
     * @return RequestedAttribute[]|null
     */
    public function getRequestedAttributes(): ?array
    {
        return $this->entity->requestedAttributes;
    }

    /**
     * @param $locale
     * @return string|null
     */
    public function getSupportUrl($locale): ?string
    {
        switch (true) {
            case ($locale == 'nl'):
                return $this->entity->supportUrlNl;
            case ($locale == 'en'):
                return $this->entity->supportUrlEn;
            case ($locale == 'pt'):
                return $this->entity->supportUrlPt;
        }

        return '';
    }

    /**
     * @param string $idpEntityId
     * @return bool
     */
    public function isAllowed(string $idpEntityId): bool
    {
        return $this->entity->isAllowed($idpEntityId);
    }

    /**
     * @return bool
     */
    public function isAttributeAggregationRequired(): bool
    {
        return $this->entity->isAttributeAggregationRequired();
    }
}
