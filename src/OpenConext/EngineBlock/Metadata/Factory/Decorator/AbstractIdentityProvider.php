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

use OpenConext\EngineBlock\Metadata\Discovery;
use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ConsentSettings;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Mdui;
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
     * @param $locale
     * @return string
     */
    public function getName($locale): string
    {
        return $this->entity->getName($locale);
    }

    /**
     * @param $locale
     * @return string
     */
    public function getDescription($locale): string
    {
        return $this->entity->getDescription($locale);
    }

    /**
     * @param $locale
     * @return string
     */
    public function getDisplayName($locale): string
    {
        return $this->entity->getDisplayName($locale);
    }

    /**
     * @return Logo|null
     */
    public function getLogo(): ?Logo
    {
        return $this->entity->getLogo();
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
     * @return Organization|null
     */
    public function getOrganization($locale): ?Organization
    {
        return $this->entity->getOrganization($locale);
    }

    /**
     * @param $locale
     * @return string
     */
    public function getKeywords($locale): string
    {
        return $this->entity->getKeywords($locale);
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

    public function getMdui(): Mdui
    {
        return $this->entity->getMdui();
    }

    /**
     * @return array<Discovery>
     */
    public function getDiscoveries(): array
    {
        return $this->entity->getDiscoveries();
    }
}
