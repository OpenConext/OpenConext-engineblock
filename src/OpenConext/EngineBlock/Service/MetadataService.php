<?php

namespace OpenConext\EngineBlock\Service;

use OpenConext\Component\EngineBlockMetadata\AttributeReleasePolicy;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProvider;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\EntityNotFoundException;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\Value\Saml\EntityId;

final class MetadataService
{
    /**
     * @var MetadataRepositoryInterface
     */
    private $metadataRepository;

    public function __construct(MetadataRepositoryInterface $metadataRepository)
    {
        $this->metadataRepository = $metadataRepository;
    }

    /**
     * @param EntityId $entityId
     * @return null|IdentityProvider
     */
    public function findIdentityProvider(EntityId $entityId)
    {
        try {
            $identityProvider = $this->metadataRepository->fetchIdentityProviderByEntityId($entityId->getEntityId());
        } catch (EntityNotFoundException $e) {
            return null;
        }

        return $identityProvider;
    }

    /**
     * @param EntityId $entityId
     * @return null|ServiceProvider
     */
    public function findServiceProvider(EntityId $entityId)
    {
        try {
            $serviceProvider = $this->metadataRepository->fetchServiceProviderByEntityId($entityId->getEntityId());
        } catch (EntityNotFoundException $e) {
            return null;
        }

        return $serviceProvider;
    }

    /**
     * @param EntityId $entityId
     * @return null|AttributeReleasePolicy
     */
    public function findArpForServiceProviderByEntityId(EntityId $entityId)
    {
        $serviceProvider = $this->findServiceProvider($entityId);

        if ($serviceProvider === null) {
            return null;
        }

        return $this->metadataRepository->fetchServiceProviderArp($serviceProvider);
    }
}
