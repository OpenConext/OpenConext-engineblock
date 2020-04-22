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

namespace OpenConext\EngineBlock\Metadata\Factory\Decorator;

use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ConsentSettings;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\ShibMdScope;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;

/**
 * This abstract class is used to circumvent the implementation of all methods of the IdentityProviderEntityInterface.
 * So only the methods required for the specific implementation have to be created on the decorated Entity that is
 * extended from this abstract IdP entity.
 */
abstract class AbstractIdentityProvider implements IdentityProviderEntityInterface
{

    /**
     * @var IdentityProviderEntityInterface
     */
    protected $entity;

    /**
     * @param IdentityProviderEntityInterface $entity
     */
    public function __construct(IdentityProviderEntityInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->entity->getId();
    }

    /**
     * @return string
     */
    public function getEntityId(): string
    {
        return $this->entity->getEntityId();
    }

    /**
     * @return string
     */
    public function getNameNl(): string
    {
        return $this->entity->getNameNl();
    }

    /**
     * @return string
     */
    public function getNameEn(): string
    {
        return $this->entity->getNameEn();
    }

    /**
     * @return string
     */
    public function getNamePt(): string
    {
        return $this->entity->getNamePt();
    }

    /**
     * @return string
     */
    public function getDescriptionNl(): string
    {
        return $this->entity->getDescriptionNl();
    }

    /**
     * @return string
     */
    public function getDescriptionEn(): string
    {
        return $this->entity->getDescriptionEn();
    }

    /**
     * @return string
     */
    public function getDescriptionPt(): string
    {
        return $this->entity->getDescriptionPt();
    }

    public function getDisplayNameEn(): string
    {
        if (empty($this->entity->getDisplayNameEn())) {
            return $this->entity->getNameEn();
        }
        return $this->entity->getDisplayNameEn();
    }

    public function getDisplayNameNl(): string
    {
        if (empty($this->entity->getDisplayNameNl())) {
            if (!empty($this->entity->getNameNl())) {
                return $this->entity->getNameNl();
            }
            return $this->entity->getNameEn();
        }
        return $this->entity->getDisplayNameNl();
    }

    public function getDisplayNamePt(): string
    {
        if (empty($this->entity->getDisplayNamePt())) {
            if (!empty($this->entity->getNamePt())) {
                return $this->entity->getNamePt();
            }
            return $this->entity->getNamePt();
        }
        return $this->entity->getDisplayNamePt();
    }

    /**
     * @return Logo|null
     */
    public function getLogo(): ?Logo
    {
        return $this->entity->getLogo();
    }

    /**
     * @return Organization|null
     */
    public function getOrganizationNl(): ?Organization
    {
        return $this->entity->getOrganizationNl();
    }

    /**
     * @return Organization|null
     */
    public function getOrganizationEn(): ?Organization
    {
        return $this->entity->getOrganizationEn();
    }

    /**
     * @return Organization|null
     */
    public function getOrganizationPt(): ?Organization
    {
        return $this->entity->getOrganizationPt();
    }

    /**
     * @return string
     */
    public function getKeywordsNl(): string
    {
        return $this->entity->getKeywordsNl();
    }

    /**
     * @return string
     */
    public function getKeywordsEn(): string
    {
        return $this->entity->getKeywordsEn();
    }

    /**
     * @return string
     */
    public function getKeywordsPt(): string
    {
        return $this->entity->getKeywordsPt();
    }

    /**
     * @return X509Certificate[]
     */
    public function getCertificates(): array
    {
        return $this->entity->getCertificates();
    }

    /**
     * @return string
     */
    public function getWorkflowState(): string
    {
        return $this->entity->getWorkflowState();
    }

    /**
     * @return ContactPerson[]
     */
    public function getContactPersons(): array
    {
        return $this->entity->getContactPersons();
    }

    /**
     * @return string
     */
    public function getNameIdFormat(): string
    {
        return $this->entity->getNameIdFormat();
    }

    /**
     * @return string[]
     */
    public function getSupportedNameIdFormats(): array
    {
        return $this->entity->getSupportedNameIdFormats();
    }

    /**
     * @return Service
     */
    public function getSingleLogoutService(): ?Service
    {
        return $this->entity->getSingleLogoutService();
    }

    /**
     * @return bool
     */
    public function isRequestsMustBeSigned(): bool
    {
        return $this->entity->isRequestsMustBeSigned();
    }

    /**
     * @return string
     */
    public function getManipulation(): string
    {
        return $this->entity->getManipulation();
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
        return $this->entity->isEnabledInWayf();
    }

    /**
     * @return Service[]
     */
    public function getSingleSignOnServices(): array
    {
        return $this->entity->getSingleSignOnServices();
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
        return $this->entity->getShibMdScopes();
    }
}
