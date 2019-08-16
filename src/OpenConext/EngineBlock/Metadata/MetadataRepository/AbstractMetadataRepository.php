<?php

/**
 * Copyright 2014 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Metadata\MetadataRepository;

use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Filter\FilterInterface;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\VisitorInterface;

/**
 * Class AbstractMetadataRepository
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository
 */
abstract class AbstractMetadataRepository implements MetadataRepositoryInterface
{
    /**
     * @var Filter\CompositeFilter
     */
    protected $compositeFilter;

    /**
     * @var array
     */
    protected $compositeVisitor;

    /**
     * Create a new Metadata Repository
     */
    protected function __construct()
    {
        $this->compositeFilter = new Filter\CompositeFilter();
        $this->compositeVisitor = new Visitor\CompositeVisitor();
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function appendFilter(FilterInterface $filter)
    {
        $this->compositeFilter->add($filter);
        return $this;
    }

    /**
     * @param VisitorInterface $visitor
     * @return $this
     */
    public function appendVisitor(VisitorInterface $visitor)
    {
        $this->compositeVisitor->append($visitor);
        return $this;
    }

    /**
     * @param string $entityId
     * @return ServiceProvider
     * @throws EntityNotFoundException
     */
    public function fetchServiceProviderByEntityId($entityId)
    {
        $serviceProvider = $this->findServiceProviderByEntityId($entityId);

        if (!$serviceProvider) {
            throw new EntityNotFoundException(sprintf('Service Provider "%s" not found in InMemoryMetadataRepository', $entityId));
        }

        return $serviceProvider;
    }

    /**
     * @param $entityId
     * @return null|IdentityProvider|ServiceProvider
     * @throws EntityNotFoundException
     */
    public function fetchIdentityProviderByEntityId($entityId)
    {
        $identityProvider = $this->findIdentityProviderByEntityId($entityId);

        if (!$identityProvider) {
            throw new EntityNotFoundException(
                sprintf('Identity Provider "%s" not found in InMemoryMetadataRepository', $entityId)
            );
        }

        return $identityProvider;
    }
}
