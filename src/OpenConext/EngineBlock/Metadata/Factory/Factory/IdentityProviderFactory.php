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

namespace OpenConext\EngineBlock\Metadata\Factory\Factory;

use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Factory\Adapter\IdentityProviderEntity;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\EngineBlockIdentityProviderMetadata;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\IdentityProviderProxy;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\IdentityProviderStepup;
use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Factory\ValueObject\EngineBlockConfiguration;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use SAML2\Constants;

/**
 * This factory is used for instantiating an entity with the required adapters and/or decorators set.
 * It also makes sure that static, internally used, entities can be generated without the use of the database.
 */
class IdentityProviderFactory
{
    /**
     * @var KeyPairFactory
     */
    private $keyPairFactory;

    /**
     * @var EngineBlockConfiguration
     */
    private $engineBlockConfiguration;

    public function __construct(KeyPairFactory $keyPairFactory, EngineBlockConfiguration $engineBlockConfiguration)
    {
        $this->keyPairFactory = $keyPairFactory;
        $this->engineBlockConfiguration = $engineBlockConfiguration;
    }

    public function createEntityFromEntity(IdentityProvider $entity): IdentityProviderEntityInterface
    {
        return new IdentityProviderEntity($entity);
    }

    public function createProxyFromEntity(IdentityProvider $entity, X509KeyPair $proxyKeyPair): IdentityProviderEntityInterface
    {
        return new IdentityProviderProxy($this->createEntityFromEntity($entity), $proxyKeyPair);
    }

    public function createStepupFromEntity(IdentityProvider $entity): IdentityProviderEntityInterface
    {
        return new IdentityProviderStepup($this->createEntityFromEntity($entity));
    }

    public function createEngineBlockEntityFrom(
        string $entityId,
        string $ssoLocation,
        string $keyId
    ): IdentityProviderEntityInterface {
        $entity = $this->buildIdentityProviderEntity($entityId, $ssoLocation, $keyId);
        // Load the additional EB SP metadata onto the entity
        $entity->nameEn = $this->engineBlockConfiguration->getName();
        $entity->nameNl = $this->engineBlockConfiguration->getName();
        $entity->descriptionEn = $this->engineBlockConfiguration->getDescription();
        $entity->descriptionNl = $this->engineBlockConfiguration->getDescription();
        $entity->organizationEn = $this->engineBlockConfiguration->getOrganization();
        $entity->contactPersons = $this->engineBlockConfiguration->getContactPersons();
        $entity->logo = $this->engineBlockConfiguration->getLogo();
        return new EngineBlockIdentityProviderMetadata($this->createEntityFromEntity($entity));
    }

    public function createMinimalEntity(
        string $entityId,
        string $ssoLocation,
        string $keyId,
        string $ssoBindingMethod = Constants::BINDING_HTTP_REDIRECT
    ): IdentityProviderEntityInterface {
        $entity = new IdentityProvider($entityId);
        $entity->singleSignOnServices[] = new Service($ssoLocation, $ssoBindingMethod);
        $entity->certificates[] = $this->keyPairFactory->buildFromIdentifier($keyId)->getCertificate();

        return $this->createEntityFromEntity($entity);
    }

    private function buildIdentityProviderEntity(
        string $entityId,
        string $ssoLocation,
        string $keyId
    ): IdentityProvider {
        $singleSignOnServices[] = new Service($ssoLocation, Constants::BINDING_HTTP_REDIRECT);

        $entity = new IdentityProvider($entityId);
        $entity->certificates[] = $this->keyPairFactory->buildFromIdentifier($keyId)->getCertificate();
        $entity->singleSignOnServices = $singleSignOnServices;

        return $entity;
    }
}
