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

use OpenConext\EngineBlock\Entities\Adapter\IdentityProviderEntity;
use OpenConext\EngineBlock\Entities\Decorator\IdentityProviderProxy;
use OpenConext\EngineBlock\Entities\Decorator\IdentityProviderStepup;
use OpenConext\EngineBlock\Entities\IdentityProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509Certificate;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use SAML2\Constants;

class IdentityProviderFactory
{
    /**
     * @var X509KeyPair
     */
    private $proxyKeyPair;

    public function __construct(X509KeyPair $proxyKeyPair)
    {
        $this->proxyKeyPair = $proxyKeyPair;
    }

    public function createEntityFromEntity(IdentityProvider $entity): IdentityProviderEntityInterface
    {
        return new IdentityProviderEntity($entity);
    }

    public function createProxyFromEntity(IdentityProvider $entity): IdentityProviderEntityInterface
    {
        return new IdentityProviderProxy($this->createEntityFromEntity($entity), $this->proxyKeyPair);
    }

    public function createStepupFromEntity(IdentityProvider $entity): IdentityProviderEntityInterface
    {
        return new IdentityProviderStepup($this->createEntityFromEntity($entity));
    }

    public function createMinimalEntity(
        string $entityId,
        string $ssoLocation,
        X509Certificate $certificate,
        string $ssoBindingMethod = Constants::BINDING_HTTP_REDIRECT
    ): IdentityProviderEntityInterface {
        $entity = new IdentityProvider($entityId);
        $entity->singleSignOnServices[] = new Service($ssoLocation, $ssoBindingMethod);
        $entity->certificates[] = $certificate;

        return $this->createEntityFromEntity($entity);
    }
}
