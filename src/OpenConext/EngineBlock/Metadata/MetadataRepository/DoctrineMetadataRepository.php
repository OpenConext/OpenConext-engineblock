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

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Webmozart\Assert\Assert;

/**
 * Class DoctrineMetadataRepository
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DoctrineMetadataRepository extends AbstractMetadataRepository
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $spRepository;

    /**
     * @var EntityRepository
     */
    private $idpRepository;

    /**
     * @param EntityManager $entityManager
     * @param EntityRepository $spRepository
     * @param EntityRepository $idpRepository
     */
    public function __construct(
        EntityManager $entityManager,
        EntityRepository $spRepository,
        EntityRepository $idpRepository
    ) {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->spRepository  = $spRepository;
        $this->idpRepository = $idpRepository;
    }

    /**
     *
     * @param array $scope
     * @return string[]
     */
    public function findAllIdentityProviderEntityIds(array $scope = [])
    {
        $queryBuilder = $this->idpRepository
            ->createQueryBuilder('role')
            ->select('role.entityId');

        if (!empty($scope)) {
            $queryBuilder
                ->where('role.entityId IN (:scopedEntityIds)')
                ->setParameter('scopedEntityIds', $scope);
        }

        $this->compositeFilter->toQueryBuilder($queryBuilder, $this->idpRepository->getClassName());

        return array_map('current', $queryBuilder->getQuery()->execute(null, AbstractQuery::HYDRATE_ARRAY));
    }

    /**
     * Find all SchacHomeOrganizations that are reserved by Identity Providers.
     *
     * @return string[]
     */
    public function findReservedSchacHomeOrganizations()
    {
        $queryBuilder = $this->idpRepository
            ->createQueryBuilder('role')
            ->select('role.schacHomeOrganization')
            ->distinct()
            ->orderBy('role.schacHomeOrganization');

        $this->compositeFilter->toQueryBuilder($queryBuilder, $this->idpRepository->getClassName());

        return $queryBuilder
            ->getQuery()
            ->execute();
    }

    /**
     * @param array $identityProviderIds
     * @return array|IdentityProvider[]
     * @throws EntityNotFoundException
     */
    public function findIdentityProvidersByEntityId(array $identityProviderIds)
    {
        $queryBuilder = $this->idpRepository->createQueryBuilder('role')
            ->andWhere('role.entityId IN (:ids)')
            ->setParameter('ids', $identityProviderIds);

        $this->compositeFilter->toQueryBuilder($queryBuilder, $this->idpRepository->getClassName());

        $identityProviders = $queryBuilder->getQuery()->execute();

        foreach ($identityProviders as $identityProvider) {
            $identityProvider->accept($this->compositeVisitor);
        }

        return $identityProviders;
    }

    /**
     * @param string $entityId
     * @return IdentityProvider|null
     */
    public function findIdentityProviderByEntityId(string $entityId)
    {
        $queryBuilder = $this->idpRepository->createQueryBuilder('role')
            ->andWhere('role.entityId = :id')
            ->setParameter('id', $entityId);

        $this->compositeFilter->toQueryBuilder($queryBuilder, $this->idpRepository->getClassName());

        $result = $queryBuilder->getQuery()->execute();

        if (empty($result)) {
            return null;
        }

        if (count($result) > 1) {
            throw new RuntimeException(sprintf('Multiple Identity Providers found for entityId: "%s"', $entityId));
        }

        $identityProvider = reset($result);
        $identityProvider->accept($this->compositeVisitor);

        return $identityProvider;
    }

    /**
     * @param string $hash
     * @return string|null
     */
    public function findIdentityProviderEntityIdByMd5Hash($hash)
    {
        $queryBuilder = $this->idpRepository->createQueryBuilder('role')
            ->select('role.entityId')
            ->andWhere('MD5(role.entityId) = :hash')
            ->setParameter('hash', $hash);

        $this->compositeFilter->toQueryBuilder($queryBuilder, $this->idpRepository->getClassName());

        $result = $queryBuilder->getQuery()->execute();

        if (empty($result)) {
            return null;
        }

        if (count($result) > 1) {
            throw new RuntimeException(sprintf('Multiple Identity Providers found for entityId MD5 hash: "%s"', $hash));
        }

        return reset($result)['entityId'];
    }

    /**
     * @param $entityId
     * @param LoggerInterface|null $logger
     * @return null|ServiceProvider
     */
    public function findServiceProviderByEntityId(string $entityId, LoggerInterface $logger = null)
    {
        $queryBuilder = $this->spRepository->createQueryBuilder('role')
            ->andWhere('role.entityId = :id')
            ->setParameter('id', $entityId);

        $this->compositeFilter->toQueryBuilder($queryBuilder, $this->spRepository->getClassName());

        $result = $queryBuilder->getQuery()->execute();

        if (empty($result)) {
            return null;
        }

        if (count($result) > 1) {
            throw new RuntimeException(sprintf('Multiple Service Providers found for entityId: "%s"', $entityId));
        }

        $serviceProvider = reset($result);
        $serviceProvider->accept($this->compositeVisitor);

        return $serviceProvider;
    }

    /**
     * @return IdentityProvider[]
     */
    public function findIdentityProviders()
    {
        $queryBuilder = $this->idpRepository->createQueryBuilder('role');

        $this->compositeFilter->toQueryBuilder($queryBuilder, $this->idpRepository->getClassName());

        $identityProviders = $queryBuilder->getQuery()->execute();

        foreach ($identityProviders as $identityProvider) {
            $identityProvider->accept($this->compositeVisitor);
        }

        return $identityProviders;
    }
}
