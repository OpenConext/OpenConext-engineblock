<?php

namespace OpenConext\EngineBlock\Metadata\MetadataRepository;

use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Filter\FilterInterface;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\VisitorInterface;
use Psr\Log\LoggerInterface;

/**
 * Interface MetadataRepositoryInterface
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository
 */
interface MetadataRepositoryInterface
{
    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function appendFilter(FilterInterface $filter);

    /**
     * @param VisitorInterface $visitor
     * @return $this
     */
    public function appendVisitor(VisitorInterface $visitor);

    /**
     *
     * @param string $entityId
     * @return AbstractRole
     * @throws EntityNotFoundException
     */
    public function fetchEntityByEntityId($entityId);

    /**
     * @param string $entityId
     * @return ServiceProvider
     * @throws EntityNotFoundException
     */
    public function fetchServiceProviderByEntityId($entityId);

    /**
     * @param string $entityId
     * @return IdentityProvider
     */
    public function fetchIdentityProviderByEntityId($entityId);

    /**
     * @deprecated depends on repository implementation.
     *
     * @param string $entityId
     * @return AbstractRole|null
     */
    public function findEntityByEntityId($entityId);

    /**
     * @param string $entityId
     * @return IdentityProvider|null
     */
    public function findIdentityProviderByEntityId($entityId);

    /**
     * @param $entityId
     * @param LoggerInterface|null $logger
     * @return null|ServiceProvider
     */
    public function findServiceProviderByEntityId($entityId, LoggerInterface $logger = null);

    /**
     * @return IdentityProvider[]
     */
    public function findIdentityProviders();

    /**
     * @param array $identityProviderEntityIds
     * @return IdentityProvider[]
     */
    public function findIdentityProvidersByEntityId(array $identityProviderEntityIds);

    /**
     * @return string[]
     */
    public function findAllIdentityProviderEntityIds();

    /**
     * @return string[]
     */
    public function findReservedSchacHomeOrganizations();

    /**
     * @return AbstractRole[]
     */
    public function findEntitiesPublishableInEdugain();

    /**
     * @param AbstractRole $entity
     * @return string
     */
    public function fetchEntityManipulation(AbstractRole $entity);

    /**
     * @param ServiceProvider $serviceProvider
     * @return AttributeReleasePolicy
     */
    public function fetchServiceProviderArp(ServiceProvider $serviceProvider);

    /**
     * @param ServiceProvider $serviceProvider
     * @return array
     */
    public function findAllowedIdpEntityIdsForSp(ServiceProvider $serviceProvider);
}
