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

use EngineBlock_Attributes_Metadata as AttributesMetadata;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Factory\Adapter\ServiceProviderEntity;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\EngineBlockServiceProviderInformation;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\EngineBlockServiceProviderMetadata;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\ServiceProviderProxy;
use OpenConext\EngineBlock\Metadata\Factory\Decorator\ServiceProviderStepup;
use OpenConext\EngineBlock\Metadata\Factory\ServiceProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Factory\ValueObject\EngineBlockConfiguration;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlockBundle\Url\UrlProvider;
use SAML2\Constants;

/**
 * This factory is used for instantiating an entity with the required adapters and/or decorators set.
 * It also makes sure that static, internally used, entities can be generated without the use of the database.
 */
class ServiceProviderFactory
{
    /**
     * @var AttributesMetadata
     */
    private $attributes;

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

    public function __construct(
        AttributesMetadata $attributes,
        KeyPairFactory $keyPairFactory,
        EngineBlockConfiguration $engineBlockConfiguration,
        UrlProvider $urlProvider
    ) {
        $this->attributes = $attributes;
        $this->keyPairFactory = $keyPairFactory;
        $this->engineBlockConfiguration = $engineBlockConfiguration;
        $this->urlProvider = $urlProvider;
    }

    public function createEngineBlockEntityFrom(
        string $entityId,
        string $keyId
    ): ServiceProviderEntityInterface {
        $entity = $this->buildServiceProviderOrmEntity($entityId);

        return new EngineBlockServiceProviderMetadata( // Add metadata helper functions for presenting data
            new ServiceProviderProxy( // Add EB proxy data
                new EngineBlockServiceProviderInformation(  // Add EB specific information
                    new ServiceProviderEntity($entity),
                    $this->engineBlockConfiguration
                ),
                $this->keyPairFactory->buildFromIdentifier($keyId),
                $this->attributes,
                $this->urlProvider
            )
        );
    }

    public function createStepupEntityFrom(
        string $entityId,
        string $keyId
    ): ServiceProviderEntityInterface {
        $entity = $this->buildServiceProviderOrmEntity($entityId);

        return new EngineBlockServiceProviderMetadata( // Add metadata helper functions for presenting data
            new ServiceProviderStepup( // Add stepup data
                new ServiceProviderEntity($entity),
                $this->keyPairFactory->buildFromIdentifier($keyId),
                $this->urlProvider
            )
        );
    }

    private function buildServiceProviderOrmEntity(
        string $entityId
    ): ServiceProvider {
        $entity = new ServiceProvider($entityId);
        return $entity;
    }
}
