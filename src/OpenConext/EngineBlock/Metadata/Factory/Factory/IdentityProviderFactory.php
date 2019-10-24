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
use OpenConext\EngineBlock\Metadata\Factory\Decorator\EngineBlockIdentityProviderInformation;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\EngineBlockIdentityProviderMetadata;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\IdentityProviderProxy;
use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Factory\ValueObject\EngineBlockConfiguration;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlockBundle\Url\UrlProvider;
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

    /**
     * @var UrlProvider
     */
    private $urlProvider;

    public function __construct(KeyPairFactory $keyPairFactory, EngineBlockConfiguration $engineBlockConfiguration, UrlProvider $urlProvider)
    {
        $this->keyPairFactory = $keyPairFactory;
        $this->engineBlockConfiguration = $engineBlockConfiguration;
        $this->urlProvider = $urlProvider;
    }

    /**
     * Use this method to create an entity which could act as proxy
     */
    public function createEngineBlockEntityFrom(string $keyId): IdentityProviderEntityInterface
    {
        $entityId = $this->urlProvider->getUrl('metadata_idp', false, null, null);

        $entity = $this->buildIdentityProviderOrmEntity($entityId);

        return $this->buildEngineBlockEntityFromEntity($entity, $keyId);
    }

    /**
     * Use this method to create an entity which could act as proxy
     */
    public function createEngineBlockEntityFromEntity(IdentityProvider $entity, string $keyId): IdentityProviderEntityInterface
    {
        return $this->buildEngineBlockEntityFromEntity($entity, $keyId);
    }

    private function buildIdentityProviderOrmEntity(string $entityId): IdentityProvider
    {
        $entity = new IdentityProvider($entityId);
        return $entity;
    }

    /**
     * This method will create an EngineBlock entity from a regular entity
     * On the returned entity all values are replaced by values where EB is acting as proxy
     *
     * - IdentityProviderEntity: The adapter to convert the ORM entity to support the immutable IdentityProviderEntityInterface interface
     * - EngineBlockIdentityProviderInformation: Information used to add EB contact and UI info
     * - IdentityProviderProxy: Set the functional fields to act as proxy:
     *   (signing certificate, supported nameid formats, sso/slo services, response processing service)
     */
    private function buildEngineBlockEntityFromEntity(IdentityProvider $entity, string $keyId): IdentityProviderEntityInterface
    {
        return new EngineBlockIdentityProviderMetadata( // Add metadata helper functions for presenting data
            new IdentityProviderProxy(  // Add EB proxy data
                new EngineBlockIdentityProviderInformation( // Add EB specific information
                    new IdentityProviderEntity($entity),
                    $this->engineBlockConfiguration
                ),
                $this->keyPairFactory->buildFromIdentifier($keyId),
                $this->urlProvider
            )
        );
    }
}
