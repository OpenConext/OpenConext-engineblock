<?php

namespace OpenConext\EngineBlockBridge\Metadata;

use OpenConext\Component\EngineBlockMetadata\Container\ContainerInterface;
use OpenConext\Component\EngineBlockMetadata\Entity\AbstractRole;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\Filter\FilterInterface;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\Visitor\VisitorInterface;
use OpenConext\EngineBlock\Exception\LogicException;
use OpenConext\EngineBlock\Service\MetadataService;
use OpenConext\Value\Saml\Entity;
use OpenConext\Value\Saml\EntityId;
use OpenConext\Value\Saml\EntitySet;
use OpenConext\Value\Saml\EntityType;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) has to adhere to the interface
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) has to adhere to the interface
 *
 * @deprecated Use the \OpenConext\EngineBlock\Service\MetadataService instead (serviceId: engineblock.service.metadata)
 */
class MetadataServiceAdapter implements MetadataRepositoryInterface
{
    /**
     * @var MetadataService
     */
    private $metadataService;

    public function __construct(MetadataService $metadataService)
    {
        $this->metadataService = $metadataService;
    }

    public static function createFromConfig(array $repositoryConfig, ContainerInterface $container)
    {
        throw new LogicException('MetadataServiceAdapter::createFromConfig may not be called');
    }

    public function appendFilter(FilterInterface $filter)
    {
        throw new LogicException('MetadataServiceAdapter::appendFilter may not be called');
    }

    public function appendVisitor(VisitorInterface $visitor)
    {
        throw new LogicException('MetadataServiceAdapter::appendVisitor may not be called');
    }

    /**
     * @param string $entityId
     * @return mixed
     *
     * @todo must be limited to type to allows SP/IdP with same entityId
     */
    public function fetchEntityByEntityId($entityId)
    {
        return $this->metadataService->getBy(new EntityId($entityId));
    }

    /**
     * @param string $entityId
     * @return ServiceProvider
     */
    public function fetchServiceProviderByEntityId($entityId)
    {
        return $this->metadataService->getServiceProvider(new EntityId($entityId));
    }

    /**
     * @param string $entityId
     * @return IdentityProvider
     */
    public function fetchIdentityProviderByEntityId($entityId)
    {
        return $this->metadataService->getIdentityProvider(new EntityId($entityId));
    }

    /**
     * @param string $entityId
     * @return null|ServiceProvider|IdentityProvider
     *
     * @todo must be limited to type to allows SP/IdP with same entityId
     */
    public function findEntityByEntityId($entityId)
    {
        return $this->metadataService->findBy(new EntityId($entityId));
    }

    /**
     * @param string $entityId
     * @return null|IdentityProvider
     */
    public function findIdentityProviderByEntityId($entityId)
    {
        return $this->metadataService->findIdentityProvider(new EntityId($entityId));
    }

    /**
     * @param $entityId
     * @return null|ServiceProvider
     */
    public function findServiceProviderByEntityId($entityId)
    {
        return $this->metadataService->findServiceProvider(new EntityId($entityId));
    }

    /**
     * @return IdentityProvider[]
     */
    public function findIdentityProviders()
    {
        return $this->metadataService->getAllIdentityProviders();
    }

    public function findIdentityProvidersByEntityId(array $identityProviderEntityIds)
    {
        $entityIds = array_map(function ($entityId) {
            new Entity(new EntityId($entityId), EntityType::IdP());
        }, $identityProviderEntityIds);

        return $this->metadataService->getIdentityProviders(new EntitySet($entityIds));
    }

    public function findAllIdentityProviderEntityIds()
    {
        return $this->metadataService->getAllIdentityProviderEntityIds();
    }

    public function findReservedSchacHomeOrganizations()
    {
        trigger_error(
            'MetadataRepositoryInterface::findReservedSchacHomeOrganizations has been deprecated and may not be used',
            E_USER_DEPRECATED
        );
    }

    public function findEntitiesPublishableInEdugain(MetadataRepositoryInterface $repository = null)
    {
        trigger_error(
            'MetadataRepositoryInterface::findEntitiesPublishableInEdugain has been deprecated and may not be used',
            E_USER_DEPRECATED
        );
    }

    public function fetchEntityManipulation(AbstractRole $entity)
    {
        // TODO: Implement fetchEntityManipulation() method.
    }

    public function fetchServiceProviderArp(ServiceProvider $serviceProvider)
    {
        // TODO: Implement fetchServiceProviderArp() method.
    }

    public function findAllowedIdpEntityIdsForSp(ServiceProvider $serviceProvider)
    {
        $this->metadataService->getConnectedIdentityProvidersFor($serviceProvider);
    }
}
