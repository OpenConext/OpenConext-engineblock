<?php

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

namespace OpenConext\EngineBlock\Service;

use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\EntityNotFoundException;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\Value\Saml\EntityId;

final class MetadataService
{
    /**
     * @var MetadataRepositoryInterface
     */
    private $metadataRepository;

    public function __construct(MetadataRepositoryInterface $metadataRepository)
    {
        $this->metadataRepository = $metadataRepository;
    }

    /**
     * @param EntityId $entityId
     * @return null|IdentityProvider
     */
    public function findIdentityProvider(EntityId $entityId)
    {
        try {
            $identityProvider = $this->metadataRepository->fetchIdentityProviderByEntityId($entityId->getEntityId());
        } catch (EntityNotFoundException $e) {
            return null;
        }

        return $identityProvider;
    }

    /**
     * @param EntityId $entityId
     * @return null|ServiceProvider
     */
    public function findServiceProvider(EntityId $entityId)
    {
        try {
            $serviceProvider = $this->metadataRepository->fetchServiceProviderByEntityId($entityId->getEntityId());
        } catch (EntityNotFoundException $e) {
            return null;
        }

        return $serviceProvider;
    }

    /**
     * @param EntityId $entityId
     * @return null|AttributeReleasePolicy
     */
    public function findArpForServiceProviderByEntityId(EntityId $entityId)
    {
        $serviceProvider = $this->findServiceProvider($entityId);

        if ($serviceProvider === null) {
            return null;
        }

        return $serviceProvider->getAttributeReleasePolicy();
    }
}
