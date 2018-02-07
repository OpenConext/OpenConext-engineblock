<?php

namespace OpenConext\EngineBlock\Service;

use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\EntityNotFoundException;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
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

        return $serviceProvider->getAttributeReleasePolicy();
    }
}
