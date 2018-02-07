<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository;

use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Filter\FilterInterface;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\VisitorInterface;
use Psr\Log\LoggerInterface;

/**
 * Caching wrapper around DoctrineMetadataRepository.
 *
 * This repository acts as the regular DoctrineRepository, but caches the
 * result of each method invocation in-memory so queries are never executed
 * more than once per request.
 *
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository
 *
 * @SuppressWarnings(PMD.TooManyPublicMethods)
 */
class CachedDoctrineMetadataRepository implements MetadataRepositoryInterface
{
    /**
     * Query result cache.
     *
     * @var array
     */
    private $cache = array();

    /**
     * @var DoctrineMetadataRepository
     */
    private $repository = array();

    /**
     * @param DoctrineMetadataRepository $repository
     */
    public function __construct(DoctrineMetadataRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Read results from cache or proxy to wrapped doctrine repository.
     *
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function invoke($name, array $args)
    {
        $signature = $name . ':' . serialize($args);

        if (!isset($this->cache[$signature])) {
            $this->cache[$signature] = call_user_func_array(array($this->repository, $name), $args);
        }

        return $this->cache[$signature];
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function appendFilter(FilterInterface $filter)
    {
        $this->repository->appendFilter($filter);

        return $this;
    }

    /**
     * @param VisitorInterface $visitor
     * @return $this
     */
    public function appendVisitor(VisitorInterface $visitor)
    {
        $this->repository->appendVisitor($visitor);

        return $this;
    }

    /**
     * @param string $entityId
     * @return AbstractRole
     * @throws EntityNotFoundException
     */
    public function fetchEntityByEntityId($entityId)
    {
        $entity = $this->findEntityByEntityId($entityId);

        if (!$entity) {
            throw new EntityNotFoundException("Entity '$entityId' not found in database");
        }

        return $entity;
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
            throw new EntityNotFoundException("Service Provider '$entityId' not found in database");
        }

        return $serviceProvider;
    }

    /**
     * @param string $entityId
     * @return IdentityProvider
     */
    public function fetchIdentityProviderByEntityId($entityId)
    {
        $identityProvider = $this->findIdentityProviderByEntityId($entityId);

        if (!$identityProvider) {
            throw new EntityNotFoundException("Identity Provider '$entityId' not found in database");
        }

        return $identityProvider;
    }

    /**
     * @deprecated Don't use this method: entity ID is NOT unique, in theory,
     *             service- and identity providers can share the same entity ID.
     *
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
    }

    /**
     * @param string $entityId
     * @return IdentityProvider|null
     */
    public function findIdentityProviderByEntityId($entityId)
    {
        return $this->invoke(__FUNCTION__, func_get_args());
    }

    /**
     * @param $entityId
     * @param LoggerInterface|null $logger
     * @return null|ServiceProvider
     */
    public function findServiceProviderByEntityId($entityId, LoggerInterface $logger = null)
    {
        return $this->invoke(__FUNCTION__, func_get_args());
    }

    /**
     * @return IdentityProvider[]
     */
    public function findIdentityProviders()
    {
        return $this->invoke(__FUNCTION__, func_get_args());
    }

    /**
     * @param array $identityProviderEntityIds
     * @return IdentityProvider[]
     */
    public function findIdentityProvidersByEntityId(array $identityProviderEntityIds)
    {
        return $this->invoke(__FUNCTION__, func_get_args());
    }

    /**
     * @return string[]
     */
    public function findAllIdentityProviderEntityIds()
    {
        return $this->invoke(__FUNCTION__, func_get_args());
    }

    /**
     * @return string[]
     */
    public function findReservedSchacHomeOrganizations()
    {
        return $this->invoke(__FUNCTION__, func_get_args());
    }

    /**
     * @return AbstractRole[]
     */
    public function findEntitiesPublishableInEdugain()
    {
        return $this->invoke(__FUNCTION__, func_get_args());
    }

    /**
     * @param ServiceProvider $serviceProvider
     * @return array
     */
    public function findAllowedIdpEntityIdsForSp(ServiceProvider $serviceProvider)
    {
        // This is a simple getter. Cache this.
        return $this->repository->findAllowedIdpEntityIdsForSp($serviceProvider);
    }
}
