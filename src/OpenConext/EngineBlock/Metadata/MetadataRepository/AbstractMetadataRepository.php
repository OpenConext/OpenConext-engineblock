<?php

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
