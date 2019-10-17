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
use OpenConext\EngineBlock\Metadata\Factory\ServiceProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Factory\ValueObject\EngineBlockConfiguration;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use PHPUnit\Framework\TestCase;
use SAML2\Constants;

class ServiceProviderFactoryTest extends TestCase
{
    /**
     * @var IdentityProviderFactory
     */
    private $factory;

    public function setup()
    {
        $attributes = $this->createMock(AttributesMetadata::class);
        $keyPairFactory = $this->createMock(KeyPairFactory::class);
        $configuration = $this->createMock(EngineBlockConfiguration::class);

        $this->factory = new ServiceProviderFactory($attributes, $keyPairFactory, $configuration);
    }


    public function test_create_entity_from_entity()
    {
        $entity = new ServiceProvider('entityId');
        $entity = $this->factory->createEntityFromEntity($entity);

        $this->assertInstanceOf(ServiceProviderEntityInterface::class, $entity);
    }

    public function test_create_proxy_from_entity()
    {
        $proxyKeyPair = $this->createMock(X509KeyPair::class);
        $consentService = $this->createMock(Service::class);

        $entity = new ServiceProvider('entityId');
        $entity = $this->factory->createProxyFromEntity($entity, $proxyKeyPair,$consentService);

        $this->assertInstanceOf(ServiceProviderEntityInterface::class, $entity);
    }

    public function test_create_minimal_entity()
    {
        $certificate = $this->createMock(X509Certificate::class);

        $entity = $this->factory->createMinimalEntity(
            'entityId',
            'acsLocation',
            'default',
            Constants::BINDING_HTTP_POST
        );

        $this->assertInstanceOf(ServiceProviderEntityInterface::class, $entity);
    }
}