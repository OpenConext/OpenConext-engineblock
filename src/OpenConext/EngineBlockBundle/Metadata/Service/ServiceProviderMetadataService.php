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

namespace OpenConext\EngineBlockBundle\Metadata\Service;

use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlockBundle\Exception\EntityCanNotBeFoundException;
use OpenConext\EngineBlockBundle\Metadata\MetadataFactory;

class ServiceProviderMetadataService implements MetadataServiceInterface
{
    /**
     * @var MetadataFactory
     */
    private $factory;

    /**
     * @var MetadataRepositoryInterface
     */
    private $metadataRepository;

    /**
     * @param MetadataFactory $factory
     * @param MetadataRepositoryInterface $metadataRepository
     */
    public function __construct(MetadataFactory $factory, MetadataRepositoryInterface $metadataRepository)
    {
        $this->factory = $factory;
        $this->metadataRepository = $metadataRepository;
    }

    /**
     * Generate XML metadata for a role (either IdP or SP)
     *
     * @param string $entityId
     * @param string $keyId
     * @return string
     */
    public function metadataFor(string $entityId, string $keyId): string
    {
        $serviceProvider = $this->metadataRepository->findServiceProviderByEntityId($entityId);

        if ($serviceProvider) {
            $this->factory->setKey($keyId);
            return $this->factory->fromServiceProviderEntity($serviceProvider);
        }
        throw new EntityCanNotBeFoundException(sprintf('Unable to find the SP with entity ID "%s".', $entityId));
    }
}
