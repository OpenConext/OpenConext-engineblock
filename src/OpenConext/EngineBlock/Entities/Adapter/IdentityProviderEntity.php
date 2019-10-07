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

namespace OpenConext\EngineBlock\Entities\Adapter;

use DateTime;
use OpenConext\EngineBlock\Entities\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ConsentSettings;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;

class IdentityProviderEntity implements IdentityProviderEntityInterface
{
    /**
     * @var IdentityProvider
     */
    private $entity;

    public function __construct(IdentityProvider $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return int
     */
    public function getId(): int
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
     * @return string
     */
    public function getNameNl(): string
    {
        return $this->entity->nameNl;
    }

    /**
     * @return string
     */
    public function getNameEn(): string
    {
        return $this->entity->nameEn;
    }

    /**
     * @return string
     */
    public function getDescriptionNl(): string
    {
        return $this->entity->descriptionNl;
    }

    /**
     * @return string
     */
    public function getDescriptionEn(): string
    {
        return $this->entity->descriptionEn;
    }

    /**
     * @return string
     */
    public function getDisplayNameNl(): string
    {
        return $this->entity->displayNameNl;
    }

    /**
     * @return string
     */
    public function getDisplayNameEn(): string
    {
        return $this->entity->displayNameEn;
    }

    /**
     * @return Logo
     */
    public function getLogo(): Logo
    {
        return $this->entity->logo;
    }

    /**
     * @return Organization
     */
    public function getOrganizationNl(): Organization
    {
        return $this->entity->organizationNl;
    }

    /**
     * @return Organization
     */
    public function getOrganizationEn(): Organization
    {
        return $this->entity->organizationEn;
    }

    /**
     * @return string
     */
    public function getKeywordsNl(): string
    {
        return $this->entity->keywordsNl;
    }

    /**
     * @return string
     */
    public function getKeywordsEn(): string
    {
        return $this->entity->keywordsEn;
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
     * @return string
     */
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

    /**
     * @return Service
     */
    public function getSingleLogoutService(): Service
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
    public function getResponseProcessingService(): string
    {
        return $this->entity->responseProcessingService;
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

    /**
     * @return bool
     */
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

    /**
     * @return ConsentSettings
     */
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
}
