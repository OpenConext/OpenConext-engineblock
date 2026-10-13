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

use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ConsentSettings;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;

/**
 * This IdentityProviderEntity is an immutable counterpart of the IdentityProvider Doctrine ORM Entity.
 *
 * This adapter is used to support the ORM entity by encapsulating it. This will make it easier to replace the ORM
 * entity ultimately.
 */
class IdentityProviderEntity implements IdentityProviderEntityInterface
{
    private IdentityProvider $entity;

    public function __construct(IdentityProvider $entity)
    {
        $this->entity = $entity;
    }

    public function getId(): int
    {
        return $this->entity->id;
    }

    public function getEntityId(): string
    {
        return $this->entity->entityId;
    }

    public function getName(string $locale): ?string
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

    public function getDescription(string $locale): ?string
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

    public function getOrganization(string $locale): ?Organization
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

    public function getKeywords(string $locale): ?string
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

    public function getNameIdFormat(): string
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

    public function getSingleLogoutService(): ?Service
    {
        return $this->entity->singleLogoutService;
    }

    public function isRequestsMustBeSigned(): bool
    {
        return $this->entity->requestsMustBeSigned;
    }

    public function getManipulation(): ?string
    {
        return $this->entity->manipulation;
    }

    public function getCoins(): Coins
    {
        return $this->entity->getCoins();
    }

    public function isEnabledInWayf(): bool
    {
        return $this->entity->enabledInWayf;
    }

    /**
     * @return Service[]
     */
    public function getSingleSignOnServices(): array
    {
        return $this->entity->singleSignOnServices;
    }

    public function getConsentSettings(): ConsentSettings
    {
        return $this->entity->getConsentSettings();
    }

    /**
     * @return ShibMdScope[]
     */
    public function getShibMdScopes(): array
    {
        return $this->entity->shibMdScopes;
    }

    public function getMdui(): Mdui
    {
        return $this->entity->getMdui();
    }

    public function getDiscoveries(): array
    {
        return $this->entity->getDiscoveries();
    }
}
