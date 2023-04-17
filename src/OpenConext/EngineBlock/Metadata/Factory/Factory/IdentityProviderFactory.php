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
use OpenConext\EngineBlock\Metadata\Factory\Decorator\EngineBlockIdentityProvider;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\EngineBlockIdentityProviderInformation;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\ProxiedIdentityProvider;
use OpenConext\EngineBlock\Metadata\Factory\Helper\IdentityProviderNameFallbackHelper;
use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Factory\ValueObject\EngineBlockConfiguration;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlockBundle\Url\UrlProvider;

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
     * Use this method to create a bare Engineblock IdP entity
     */
    public function createEngineBlockEntityFrom(?string $keyId): IdentityProviderEntityInterface
    {
        $entityId = $this->urlProvider->getUrl('metadata_idp', false, null, null);

        $entity = $this->buildIdentityProviderOrmEntity($entityId);

        return $this->buildEngineBlockEntityFromEntity($entity, $keyId);
    }

    /**
     * Use this method to create an IdP entity that is displayed on the IdPs metadata overview.
     */
    public function createIdentityProviderEntityFromEntity(IdentityProvider $entity, ?string $keyId): IdentityProviderEntityInterface
    {
        return $this->buildIdentityProviderFromEntity($entity, $keyId);
    }

    private function buildIdentityProviderOrmEntity(string $entityId): IdentityProvider
    {
        $entity = new IdentityProvider($entityId, Mdui::emptyMdui());
        return $entity;
    }

    /**
     * This method will create an EngineBlock entity from a regular entity
     * On the returned entity all values are replaced by values where EB is acting as proxy
     *
     * - IdentityProviderEntity: The adapter to convert the ORM entity to support the immutable IdentityProviderEntityInterface interface
     * - EngineBlockIdentityProviderInformation: Information used to add EB contact and UI info
     * - EngineBlockIdentityProvider: Set the functional fields to act as proxy:
     *   (signing certificate, supported nameid formats, sso/slo services, response processing service)
     */
    private function buildEngineBlockEntityFromEntity(IdentityProvider $entity, ?string $keyId): IdentityProviderEntityInterface
    {
        return new EngineBlockIdentityProvider(  // Set EngineBlock specific functional properties so EB could act as proxy
            new EngineBlockIdentityProviderInformation( // Set EngineBlock specific presentation properties
                new IdentityProviderEntity($entity),
                $this->engineBlockConfiguration
            ),
            $keyId,
            $this->keyPairFactory->buildFromIdentifier($keyId),
            $this->urlProvider
        );
    }

    /**
     * This method will create an IdP entity from a regular entity
     *
     * The IdP is decorated with several EngineBlock properties like the SSO location, Contact Persons and so forth.
     * This because EngineBlock proxies for these IdPs.
     *
     * - IdentityProviderEntity: The adapter to convert the ORM entity to support the immutable IdentityProviderEntityInterface interface
     * - ProxiedIdentityProvider: Set the functional fields to act as an IdP proxied by EngineBlock
     */
    private function buildIdentityProviderFromEntity(IdentityProvider $entity, ?string $keyId): IdentityProviderEntityInterface
    {
        // Set IdP specific properties where the IdP is proxied by EngineBlock. So the EB certificate, contact persons
        // and SSO location are overridden
        return new IdentityProviderNameFallbackHelper(
            new ProxiedIdentityProvider(
                new IdentityProviderEntity($entity),
                $this->engineBlockConfiguration,
                $keyId,
                $this->keyPairFactory->buildFromIdentifier($keyId),
                $this->urlProvider
            )
        );
    }
}
