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

namespace OpenConext\EngineBlock\Entities\Factory;

use EngineBlock_Attributes_Metadata as AttributesMetadata;
use OpenConext\EngineBlock\Entities\Adapter\ServiceProviderEntity;
use OpenConext\EngineBlock\Entities\Decorator\ServiceProviderProxy;
use OpenConext\EngineBlock\Entities\ServiceProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use SAML2\Constants;

class ServiceProviderFactory
{
    /**
     * @var X509KeyPair
     */
    private $proxyKeyPair;
    /**
     * @var AttributesMetadata
     */
    private $attributes;
    /**
     * @var Service
     */
    private $consentService;

    public function __construct(
        X509KeyPair $proxyKeyPair,
        AttributesMetadata $attributes,
        Service $consentService
    ) {
        $this->proxyKeyPair = $proxyKeyPair;
        $this->attributes = $attributes;
        $this->consentService = $consentService;
    }

    public function createEntityFromEntity(ServiceProvider $entity): ServiceProviderEntityInterface
    {
        return new ServiceProviderEntity($entity);
    }

    public function createProxyFromEntity(ServiceProvider $entity): ServiceProviderEntityInterface
    {
        return new ServiceProviderProxy($this->createEntityFromEntity($entity), $this->proxyKeyPair, $this->attributes, $this->consentService);
    }

    public function createMinimalEntity(
        string $entityId,
        string $acsLocation,
        X509Certificate $certificate,
        string $acsBindingMethod = Constants::BINDING_HTTP_POST
    ): ServiceProviderEntityInterface {
        $entity = new ServiceProvider($entityId);
        $entity->certificates[] = $certificate;
        $entity->assertionConsumerServices[] = new IndexedService(
            $acsLocation,
            $acsBindingMethod,
            0
        );

        return $this->createEntityFromEntity($entity);
    }
}
