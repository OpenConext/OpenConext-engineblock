<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use Psr\Log\LoggerInterface;
use RuntimeException;

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
    public function findIdentityProviderByEntityId($entityId)
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
            throw new RuntimeException('Multiple Identity Providers found for entityId: ' . $entityId);
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
            throw new RuntimeException('Multiple Identity Providers found for entityId MD5 hash: ' . $hash);
        }

        return reset($result)['entityId'];
    }

    /**
     * @param $entityId
     * @param LoggerInterface|null $logger
     * @return null|ServiceProvider
     */
    public function findServiceProviderByEntityId($entityId, LoggerInterface $logger = null)
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
            throw new RuntimeException('Multiple Service Providers found for entityId: ' . $entityId);
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

    /**
     * @return AbstractRole[]
     */
    public function findEntitiesPublishableInEdugain()
    {
        $result = array();
        $result = array_merge($result, $this->idpRepository->findBy(array('publishInEdugain' => true)));
        $result = array_merge($result, $this->spRepository->findBy(array('publishInEdugain' => true)));
        return $result;
    }

    /**
     * Synchronize the database with the provided roles.
     *
     * Any roles (idp or sp) already existing the database are updated. New
     * roles are created. All identity- or service providers in the database
     * which are NOT in the provided roles are deleted at the end of the
     * synchronization process.
     *
     * @param AbstractRole[] $roles
     * @return SynchronizationResult
     */
    public function synchronize(array $roles)
    {
        $result = new SynchronizationResult();

        $this->entityManager->transactional(function (EntityManager $em) use ($roles, $result) {
            $idpsToBeRemoved = $this->findAllIdentityProviderEntityIds();
            $spsToBeRemoved = $this->findAllServiceProviderEntityIds();

            foreach ($roles as $role) {
                if ($role instanceof IdentityProvider) {
                    // Does the IDP already exist in the database?
                    $index = array_search($role->entityId, $idpsToBeRemoved);

                    if ($index === false) {
                        // The IDP is new: create it.
                        $em->persist($role);
                        $result->createdIdentityProviders[] = $role->entityId;
                    } else {
                        // Remove from the list of entity ids so it won't get deleted later on.
                        unset($idpsToBeRemoved[$index]);

                        // The IDP already exists: update it.
                        $identityProvider = $this->findIdentityProviderByEntityId($role->entityId);
                        $role->id = $identityProvider->id;
                        $em->persist($em->merge($role));
                        $result->updatedIdentityProviders[] = $role->entityId;
                    }
                    continue;
                }

                if ($role instanceof ServiceProvider) {
                    // Does the SP already exist in the database?
                    $index = array_search($role->entityId, $spsToBeRemoved);
                    if ($index === false) {
                        // The SP is new: create it.
                        $em->persist($role);
                        $result->createdServiceProviders[] = $role->entityId;
                    } else {
                        // Remove from the list of entity ids so it won't get deleted later on.
                        unset($spsToBeRemoved[$index]);

                        // The SP already exists: update it.
                        $serviceProvider = $this->findServiceProviderByEntityId($role->entityId);
                        $role->id = $serviceProvider->id;
                        $em->persist($em->merge($role));
                        $result->updatedServiceProviders[] = $role->entityId;
                    }
                    continue;
                }

                throw new RuntimeException('Unsupported role provided to synchonization: ' . var_export($role, true));
            }

            if ($idpsToBeRemoved) {
                $this->deleteRolesByEntityIds($this->idpRepository, $idpsToBeRemoved);

                $result->removedIdentityProviders = $idpsToBeRemoved;
            }

            if ($spsToBeRemoved) {
                $this->deleteRolesByEntityIds($this->spRepository, $spsToBeRemoved);

                $result->removedServiceProviders = $spsToBeRemoved;
            }
        });

        return $result;
    }

    /**
     * @param EntityRepository $repository
     * @param array $entityIds
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    private function deleteRolesByEntityIds(EntityRepository $repository, array $entityIds)
    {
        $qb = $repository->createQueryBuilder('role')
            ->delete()
            ->where('role.entityId IN (:ids)')
            ->setParameter('ids', $entityIds);

        $qb->getQuery()->execute();
    }

    public function findAllServiceProviderEntityIds()
    {
        $queryBuilder = $this->spRepository
            ->createQueryBuilder('role')
            ->select('role.entityId');

        $this->compositeFilter->toQueryBuilder($queryBuilder, $this->spRepository->getClassName());

        return array_map('current', $queryBuilder->getQuery()->execute(null, AbstractQuery::HYDRATE_ARRAY));
    }
}
