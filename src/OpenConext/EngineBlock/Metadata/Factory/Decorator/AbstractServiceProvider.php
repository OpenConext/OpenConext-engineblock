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

use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\Coins;
use OpenConext\EngineBlock\Metadata\ContactPerson;
use OpenConext\EngineBlock\Metadata\Factory\ServiceProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Logo;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\Organization;
use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;

/**
 * This abstract class is used to circumvent the implementation of all methods of the ServiceProviderEntityInterface.
 * So only the methods required for the specific implementation have to be created on the decorated Entity that is
 * extended from this abstract SP entity.
 */
abstract class AbstractServiceProvider implements ServiceProviderEntityInterface
{

    protected ServiceProviderEntityInterface $entity;

    public function __construct(ServiceProviderEntityInterface $entity)
    {
        $this->entity = $entity;
    }

    public function getId(): ?int
    {
        return $this->entity->getId();
    }

    public function getEntityId(): string
    {
        return $this->entity->getEntityId();
    }

    public function getName(string $locale): ?string
    {
        return $this->entity->getName($locale);
    }

    public function getDescription(string $locale): ?string
    {
        return $this->entity->getDescription($locale);
    }

    public function getDisplayName(string $locale): ?string
    {
        return $this->entity->getDisplayName($locale);
    }

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

    public function getOrganization(string $locale): ?Organization
    {
        return $this->entity->getOrganization($locale);
    }

    public function getKeywords(string $locale): string
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

    public function getNameIdFormat(): ?string
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

    public function getSingleLogoutService(): ?Service
    {
        return $this->entity->getSingleLogoutService();
    }

    public function isRequestsMustBeSigned(): bool
    {
        return $this->entity->isRequestsMustBeSigned();
    }

    public function getManipulation(): ?string
    {
        return $this->entity->getManipulation();
    }

    public function getCoins(): Coins
    {
        return $this->entity->getCoins();
    }

    public function getAttributeReleasePolicy(): ?AttributeReleasePolicy
    {
        return $this->entity->getAttributeReleasePolicy();
    }

    /**
     * @return IndexedService[]
     */
    public function getAssertionConsumerServices(): array
    {
        return $this->entity->getAssertionConsumerServices();
    }

    /**
     * @return string[]
     */
    public function getAllowedIdpEntityIds(): array
    {
        return $this->entity->getAllowedIdpEntityIds();
    }

    public function isAllowAll(): bool
    {
        return $this->entity->isAllowAll();
    }

    /**
     * @return RequestedAttribute[]|null
     */
    public function getRequestedAttributes(): ?array
    {
        return $this->entity->getRequestedAttributes();
    }

    public function getSupportUrl($locale): ?string
    {
        return $this->entity->getSupportUrl($locale);
    }

    public function isAllowed(string $idpEntityId): bool
    {
        return $this->entity->isAllowed($idpEntityId);
    }

    public function isAttributeAggregationRequired(): bool
    {
        return $this->entity->isAttributeAggregationRequired();
    }

    public function getMdui(): Mdui
    {
        return $this->entity->getMdui();
    }
}
