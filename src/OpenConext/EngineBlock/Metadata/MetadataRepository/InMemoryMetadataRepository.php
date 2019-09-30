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

namespace OpenConext\EngineBlock\Metadata\MetadataRepository;

use InvalidArgumentException;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use Psr\Log\LoggerInterface;

/**
 * Class InMemoryMetadataRepository
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class InMemoryMetadataRepository extends AbstractMetadataRepository
{
    /**
     * @var ServiceProvider[]
     */
    private $serviceProviders = array();

    /**
     * @var IdentityProvider[]
     */
    private $identityProviders = array();

    /**
     * @param IdentityProvider[] $identityProviders
     * @param ServiceProvider[] $serviceProviders
     * @throws InvalidArgumentException
     */
    public function __construct(array $identityProviders, array $serviceProviders)
    {
        parent::__construct();

        foreach ($identityProviders as $identityProvider) {
            $this->registerIdentityProvider($identityProvider);
        }

        foreach ($serviceProviders as $serviceProvider) {
            $this->registerServiceProvider($serviceProvider);
        }
    }

    /**
     * @param ServiceProvider $serviceProvider
     * @return $this
     */
    public function registerServiceProvider(ServiceProvider $serviceProvider)
    {
        $this->serviceProviders[] = $serviceProvider;

        return $this;
    }

    /**
     * @param IdentityProvider $identityProvider
     * @return $this
     */
    public function registerIdentityProvider(IdentityProvider $identityProvider)
    {
        $this->identityProviders[] = $identityProvider;

        return $this;
    }

    /**
     * @param string $entityId
     * @return ServiceProvider|null
     */
    public function findIdentityProviderByEntityId($entityId)
    {
        $roles = $this->findIdentityProviders();

        foreach ($roles as $role) {
            if ($role->entityId === $entityId) {
                return $role;
            }
        }
    }

    /**
     * @param string $hash
     * @return string|null
     */
    public function findIdentityProviderEntityIdByMd5Hash($hash)
    {
        $roles = $this->findIdentityProviders();

        foreach ($roles as $role) {
            if (md5($role->entityId) === $hash) {
                return $role->entityId;
            }
        }
    }

    /**
     * @param $entityId
     * @param LoggerInterface|null $logger
     * @return null|ServiceProvider
     */
    public function findServiceProviderByEntityId($entityId, LoggerInterface $logger = null)
    {
        $roles = $this->findServiceProviders();

        foreach ($roles as $role) {
            if ($role->entityId === $entityId) {
                return $role;
            }
        }
    }

    /**
     * @return IdentityProvider[]
     */
    public function findIdentityProviders()
    {
        $identityProviders = $this->compositeFilter->filterRoles(
            $this->identityProviders
        );

        foreach ($identityProviders as $identityProvider) {
            $identityProvider->accept($this->compositeVisitor);
        }

        $indexedIdentityProviders = array();
        foreach ($identityProviders as $identityProvider) {
            $indexedIdentityProviders[$identityProvider->entityId] = $identityProvider;
        }
        return $indexedIdentityProviders;
    }

    /**
     * @return ServiceProvider[]
     */
    private function findServiceProviders()
    {
        $serviceProviders = $this->compositeFilter->filterRoles(
            $this->serviceProviders
        );

        foreach ($serviceProviders as $serviceProvider) {
            $serviceProvider->accept($this->compositeVisitor);
        }

        return $serviceProviders;
    }

    /**
     * @param array $scope
     * @return string[]
     */
    public function findAllIdentityProviderEntityIds(array $scope = [])
    {
        $identityProviders = $this->findIdentityProviders();

        $entityIds = array();
        foreach ($identityProviders as $identityProvider) {
            $entityIds[] = $identityProvider->entityId;
        }

        if (!empty($scope)) {
            $entityIds = array_intersect($entityIds, $scope);
        }

        return $entityIds;
    }

    /**
     * @return string[]
     */
    public function findReservedSchacHomeOrganizations()
    {
        $schacHomeOrganizations = array();

        $identityProviders = $this->findIdentityProviders();
        foreach ($identityProviders as $identityProvider) {
            if (!$identityProvider->getCoins()->schacHomeOrganization()) {
                continue;
            }

            $schacHomeOrganizations[] = $identityProvider->getCoins()->schacHomeOrganization();
        }
        return $schacHomeOrganizations;
    }

    /**
     * @param array $identityProviderEntityIds
     * @return array|IdentityProvider[]
     * @throws EntityNotFoundException
     */
    public function findIdentityProvidersByEntityId(array $identityProviderEntityIds)
    {
        $identityProviders = $this->findIdentityProviders();

        $filteredIdentityProviders = array();
        foreach ($identityProviderEntityIds as $identityProviderEntityId) {
            if (!isset($identityProviders[$identityProviderEntityId])) {
                continue;
            }

            $filteredIdentityProviders[$identityProviderEntityId] = $identityProviders[$identityProviderEntityId];
        }
        return $filteredIdentityProviders;
    }
}
