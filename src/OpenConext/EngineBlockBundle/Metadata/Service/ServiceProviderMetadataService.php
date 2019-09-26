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

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlockBundle\Exception\EntityCanNotBeFoundException;
use OpenConext\EngineBlockBundle\Metadata\MetadataFactory;
use Webmozart\Assert\Assert;

class ServiceProviderMetadataService implements MetadataService
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
     * ServiceProviderMetadataService constructor.
     * @param MetadataFactory $factory
     */
    public function __construct(MetadataFactory $factory, MetadataRepositoryInterface $metadataRepository)
    {
        $this->factory = $factory;
        $this->metadataRepository = $metadataRepository;
    }

    /**
     * Set the optional key (for key rollover)
     * @param string $keyId
     */
    public function setKeyId(string $keyId): void
    {
        // Todo
    }

    /**
     * Finds the Service Provider or throws an EntityCanNotBeFoundException
     * @param string $entityId
     * @return AbstractRole
     * @throws EntityCanNotBeFoundException
     */
    public function getRoleByEntityId(string $entityId): AbstractRole
    {
        $serviceProvider = $this->metadataRepository->findServiceProviderByEntityId($entityId);

        if ($serviceProvider) {
            return $serviceProvider;
        }
        throw new EntityCanNotBeFoundException(sprintf('Unable to find the SP with entity ID "%s".', $entityId));
    }

    /**
     * Generate XML metadata for a role (either IdP or SP)
     *
     * @param AbstractRole $role
     * @return string
     */
    public function metadataFrom(AbstractRole $role): string
    {
        Assert::isInstanceOf($role, ServiceProvider::class, 'Please provide a Service Provider instance');
        return $this->factory->fromServiceProviderEntity($role);
    }
}
