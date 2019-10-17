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

    public function createEngineBlockEntityFrom(
        string $entityId,
        string $ssoLocation,
        string $keyId
    ): IdentityProviderEntityInterface {
        $entity = $this->buildIdentityProviderOrmEntity($entityId, $ssoLocation, $keyId);

        return new EngineBlockIdentityProviderMetadata(
            new IdentityProviderProxy(  // Add EB proxy data
                new EngineBlockIdentityProviderInformation( // Add EB specific information
                    new IdentityProviderEntity($entity),
                    $this->engineBlockConfiguration
                ),
                $this->keyPairFactory->buildFromIdentifier($keyId)
            )
        );
    }

    private function buildIdentityProviderOrmEntity(
        string $entityId,
        string $ssoLocation,
        string $keyId
    ): IdentityProvider {
        $singleSignOnServices[] = new Service($ssoLocation, Constants::BINDING_HTTP_REDIRECT);

        $entity = new IdentityProvider($entityId);
        $entity->singleSignOnServices = $singleSignOnServices;

        return $entity;
    }
}
