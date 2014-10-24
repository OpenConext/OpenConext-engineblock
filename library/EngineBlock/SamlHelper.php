<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;
use OpenConext\Component\EngineBlockMetadata\Entity\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProviderEntity;

class EngineBlock_SamlHelper
{
    /**
     * Do we need to enable additional logging for any of the specified entities (SP or IdP).
     *
     * @param AbstractConfigurationEntity[] $entities
     * @return bool
     */
    public static function doRemoteEntitiesRequireAdditionalLogging(array $entities)
    {
        return array_reduce($entities, function($carry, AbstractConfigurationEntity $entity) {
            return $carry | $entity->additionalLogging;
        }, false);
    }

    /**
     * Get the 'chain' of SP requesters, if available. Furthest removed SP first.
     *
     * @param ServiceProviderEntity $serviceProvider
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param MetadataRepositoryInterface $repository
     * @return ServiceProviderEntity[]
     */
    public static function getSpRequesterChain(
        ServiceProviderEntity $serviceProvider,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        MetadataRepositoryInterface $repository
    ) {
        $chain = array($serviceProvider);

        $destinationSpMetadata = self::getDestinationSpMetadata($serviceProvider, $request, $repository);
        if ($destinationSpMetadata !== $serviceProvider) {
            array_unshift($chain, $destinationSpMetadata);
        }

        return $chain;
    }

    /**
     * Get the 'Destination' SP metadata. Depending on the SP configuration, may be the SP metadata or it's requester.
     *
     * @param ServiceProviderEntity $serviceProvider
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param MetadataRepositoryInterface $repository
     * @return ServiceProviderEntity
     */
    public static function getDestinationSpMetadata(
        ServiceProviderEntity $serviceProvider,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        MetadataRepositoryInterface $repository
    ) {
        $requester = self::findRequesterServiceProvider($serviceProvider, $request, $repository);
        return $requester ? $requester : $serviceProvider;
    }

    /**
     * Get the metadata for a requester, if allowed by the configuration.
     *
     * @param ServiceProviderEntity $serviceProvider
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param MetadataRepositoryInterface $repository
     * @return null|ServiceProviderEntity
     */
    public static function findRequesterServiceProvider(
        ServiceProviderEntity $serviceProvider,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        MetadataRepositoryInterface $repository
    ) {
        if (!$serviceProvider->isTrustedProxy) {
            return null;
        }

        if (!$request->wasSigned()) {
            return null;
        }

        // Requester IDs are appended to as they pass through a proxy, so we always want the last RequesterID
        // Note that this is not specified in the spec, but this is what we do and what SSP does.
        $requesterIds = $request->getRequesterIds();
        $lastRequesterEntityId = end($requesterIds);

        if (!$lastRequesterEntityId) {
            return null;
        }

        $lastRequesterEntity = $repository->findServiceProviderByEntityId($lastRequesterEntityId);
        if (!$lastRequesterEntity) {
            return null;
        }

        return $lastRequesterEntity;
    }
}
