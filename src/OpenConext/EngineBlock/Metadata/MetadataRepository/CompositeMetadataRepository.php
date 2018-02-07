<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository;

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Filter\FilterInterface;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\VisitorInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CompositeMetadataRepository
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository
 * @SuppressWarnings(PMD.TooManyMethods)
 * @SuppressWarnings(PMD.TooManyPublicMethods)
 * @SuppressWarnings(PMD.CouplingBetweenObjects)
 */
class CompositeMetadataRepository extends AbstractMetadataRepository
{
    /**
     * @var MetadataRepositoryInterface[]
     */
    private $orderedRepositories = array();

    /**
     * @param MetadataRepositoryInterface[] $orderedRepositories
     */
    public function __construct(array $orderedRepositories)
    {
        parent::__construct();

        $this->orderedRepositories = $orderedRepositories;
    }

    /**
     * @param MetadataRepositoryInterface $repository
     * @return $this
     */
    public function appendRepository(MetadataRepositoryInterface $repository)
    {
        $this->orderedRepositories[] = $repository;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function appendVisitor(VisitorInterface $visitor)
    {
        foreach ($this->orderedRepositories as $repository) {
            $repository->appendVisitor($visitor);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function appendFilter(FilterInterface $filter)
    {
        foreach ($this->orderedRepositories as $repository) {
            $repository->appendFilter(clone $filter);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchEntityByEntityId($entityId)
    {
        foreach ($this->orderedRepositories as $repository) {
            $entity = $repository->findEntityByEntityId($entityId);

            if ($entity) {
                return $entity;
            }
        }

        throw new EntityNotFoundException("Unable to find '$entityId' in any configured repository");
    }

    /**
     * {@inheritdoc}
     */
    public function fetchServiceProviderByEntityId($entityId)
    {
        foreach ($this->orderedRepositories as $repository) {
            $entity = $repository->findServiceProviderByEntityId($entityId);

            if ($entity) {
                return $entity;
            }
        }

        throw new EntityNotFoundException("Unable to find '$entityId' in any configured repository");
    }

    /**
     * {@inheritdoc}
     */
    public function fetchIdentityProviderByEntityId($entityId)
    {
        foreach ($this->orderedRepositories as $repository) {
            $entity = $repository->findIdentityProviderByEntityId($entityId);

            if ($entity) {
                return $entity;
            }
        }

        throw new EntityNotFoundException("Unable to find '$entityId' in any configured repository");
    }

    /**
     * {@inheritdoc}
     */
    public function findEntityByEntityId($entityId)
    {
        foreach ($this->orderedRepositories as $repository) {
            $entity = $repository->findEntityByEntityId($entityId);

            if ($entity) {
                return $entity;
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findIdentityProviderByEntityId($entityId)
    {
        foreach ($this->orderedRepositories as $repository) {
            $entity = $repository->findIdentityProviderByEntityId($entityId);

            if ($entity) {
                return $entity;
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findServiceProviderByEntityId($entityId, LoggerInterface $logger = null)
    {
        foreach ($this->orderedRepositories as $repository) {
            $entity = $repository->findServiceProviderByEntityId($entityId);

            if ($entity) {
                return $entity;
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findIdentityProviders()
    {
        $identityProviders = array();
        foreach ($this->orderedRepositories as $repository) {
            $repositoryIdentityProviders = $repository->findIdentityProviders();
            foreach ($repositoryIdentityProviders as $identityProvider) {
                // Earlier repositories have precedence, so if later repositories give the same entityId,
                // then we ignore that.
                if (isset($identityProviders[$identityProvider->entityId])) {
                    continue;
                }

                $identityProviders[$identityProvider->entityId] = $identityProvider;
            }
        }
        return $identityProviders;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllIdentityProviderEntityIds()
    {
        $identityProviderEntityIds = array();
        foreach ($this->orderedRepositories as $repository) {
            $identityProviderEntityIds = array_merge(
                $identityProviderEntityIds,
                $repository->findAllIdentityProviderEntityIds()
            );
        }
        return array_values(array_unique($identityProviderEntityIds));
    }

    /**
     * {@inheritdoc}
     */
    public function findReservedSchacHomeOrganizations()
    {
        $schacHomeOrganizations = array();
        foreach ($this->orderedRepositories as $repository) {
            $schacHomeOrganizations = array_merge(
                $schacHomeOrganizations,
                $repository->findReservedSchacHomeOrganizations()
            );
        }
        return array_values(array_unique($schacHomeOrganizations));
    }

    /**
     * {@inheritdoc}
     */
    public function findEntitiesPublishableInEdugain()
    {
        $entityIndex = array();
        $entities = array();
        foreach ($this->orderedRepositories as $repository) {
            $repositoryEntities = $repository->findEntitiesPublishableInEdugain();
            foreach ($repositoryEntities as $repositoryEntity) {
                // When is an entity the same as another one? For now when it's the same role type (SP / IDP)
                // and has the same entityId. Though the SAML2 spec allows for much more than that,
                // we currently don't support anything more.
                // Note that we avoid an O(n3) lookup here by maintaining an index.
                $index = get_class($repositoryEntity) . ':' . $repositoryEntity->entityId;
                if (in_array($index, $entityIndex)) {
                    continue;
                }

                $entityIndex[] = $index;
                $entities[] = $repositoryEntity;
            }
        }
        return $entities;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchServiceProviderArp(ServiceProvider $serviceProvider)
    {
        foreach ($this->orderedRepositories as $repository) {
            if (!$repository->findServiceProviderByEntityId($serviceProvider->entityId)) {
                continue;
            }

            return $repository->fetchServiceProviderArp($serviceProvider);
        }

        throw new \RuntimeException(
            __METHOD__ . ' was unable to find a repository for SP: ' . $serviceProvider->entityId
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findAllowedIdpEntityIdsForSp(ServiceProvider $serviceProvider)
    {
        $allowed = array();
        foreach ($this->orderedRepositories as $repository) {
            $allowed += $repository->findAllowedIdpEntityIdsForSp($serviceProvider);
        }

        return array_values(array_unique($allowed));
    }
}
