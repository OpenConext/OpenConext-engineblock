<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository;

use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Filter\FilterInterface;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\VisitorInterface;

/**
 * Class AbstractMetadataRepository
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository
 * @SuppressWarnings(PMD.TooManyMethods)
 * @SuppressWarnings(PMD.TooManyPublicMethods)
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
     *
     * WARNING: Very inefficient in-memory default.
     *
     * @return string[]
     */
    public function findAllIdentityProviderEntityIds()
    {
        $identityProviders = $this->findIdentityProviders();

        $entityIds = array();
        foreach ($identityProviders as $identityProvider) {
            $entityIds[] = $identityProvider->entityId;
        }

        return $entityIds;
    }

    /**
     *
     * WARNING: Very inefficient in-memory default.
     *
     * @return string[]
     */
    public function findReservedSchacHomeOrganizations()
    {
        $schacHomeOrganizations = array();

        $identityProviders = $this->findIdentityProviders();
        foreach ($identityProviders as $identityProvider) {
            if (!$identityProvider->schacHomeOrganization) {
                continue;
            }

            $schacHomeOrganizations[] = $identityProvider->schacHomeOrganization;
        }
        return $schacHomeOrganizations;
    }

    /**
     *
     *
     * WARNING: Very inefficient in-memory default.
     *
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
                // @todo warn
                continue;
            }

            $filteredIdentityProviders[$identityProviderEntityId] = $identityProviders[$identityProviderEntityId];
        }
        return $filteredIdentityProviders;
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
            throw new EntityNotFoundException("Service Provider '$entityId' not found in InMemoryMetadataRepository");
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
            throw new EntityNotFoundException("Identity Provider '$entityId' not found in InMemoryMetadataRepository");
        }

        return $identityProvider;
    }

    /**
     *
     * @param string $entityId
     * @return AbstractRole
     * @throws EntityNotFoundException
     */
    public function fetchEntityByEntityId($entityId)
    {
        $entity = $this->findEntityByEntityId($entityId);

        if (!$entity) {
            throw new EntityNotFoundException("Entity '$entityId' not found in InMemoryMetadataRepository");
        }

        return $entity;
    }

    /**
     * @param string $entityId
     * @return AbstractRole|null
     */
    public function findEntityByEntityId($entityId)
    {
        $serviceProvider = $this->findServiceProviderByEntityId($entityId);
        if ($serviceProvider) {
            return $serviceProvider;
        }

        $identityProvider = $this->findIdentityProviderByEntityId($entityId);
        if ($identityProvider) {
            return $identityProvider;
        }

        return null;
    }

    /**
     *
     * Note that this is here primarily for compatibility with JanusRestV1.
     *
     * @param AbstractRole $entity
     * @return string
     */
    public function fetchEntityManipulation(AbstractRole $entity)
    {
        return $entity->getManipulation();
    }

    /**
     *
     *
     * Note that this is here primarily for compatibility with JanusRestV1.
     *
     * @param ServiceProvider $serviceProvider
     * @return AttributeReleasePolicy
     */
    public function fetchServiceProviderArp(ServiceProvider $serviceProvider)
    {
        return $serviceProvider->getAttributeReleasePolicy();
    }

    /**
     * @param ServiceProvider $serviceProvider
     * @return \string[]
     */
    public function findAllowedIdpEntityIdsForSp(ServiceProvider $serviceProvider)
    {
        return $this->findAllIdentityProviderEntityIds();
    }
}
