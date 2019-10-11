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
use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use PHPUnit\Framework\TestCase;
use SAML2\Constants;

class IdentityProviderFactoryTest extends TestCase
{
    /**
     * @var IdentityProviderFactory
     */
    private $factory;

    public function setup()
    {
        $proxyKeyPair = $this->createMock(X509KeyPair::class);
        $this->factory = new IdentityProviderFactory($proxyKeyPair);
    }

    public function test_create_entity_from_entity()
    {
        $entity = new IdentityProvider('entityId');
        $entity = $this->factory->createEntityFromEntity($entity);

        $this->assertInstanceOf(IdentityProviderEntityInterface::class, $entity);
    }

    public function test_create_proxy_from_entity()
    {
        $entity = new IdentityProvider('entityId');
        $entity = $this->factory->createProxyFromEntity($entity);

        $this->assertInstanceOf(IdentityProviderEntityInterface::class, $entity);
    }

    public function test_create_stepup_from_entity()
    {
        $entity = new IdentityProvider('entityId');
        $entity = $this->factory->createProxyFromEntity($entity);

        $this->assertInstanceOf(IdentityProviderEntityInterface::class, $entity);
    }

    public function test_create_minimal_entity()
    {
        $certificate = $this->createMock(X509Certificate::class);

        $entity = $this->factory->createMinimalEntity(
            'entityId',
            'ssoLocation',
            $certificate,
            Constants::BINDING_HTTP_REDIRECT
        );

        $this->assertInstanceOf(IdentityProviderEntityInterface::class, $entity);
    }
}
