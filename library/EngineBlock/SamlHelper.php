<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractRole;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProvider;

class EngineBlock_SamlHelper
{
    /**
     * Do we need to enable additional logging for any of the specified entities (SP or IdP).
     *
     * @param AbstractRole[] $entities
     * @return bool
     */
    public static function doRemoteEntitiesRequireAdditionalLogging(array $entities)
    {
        return array_reduce($entities, function($carry, AbstractRole $entity) {
            return $carry | $entity->additionalLogging;
        }, false);
    }

    /**
     * Get the 'chain' of SP requesters, if available. Furthest removed SP first.
     *
     * @param ServiceProvider $serviceProvider
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param MetadataRepositoryInterface $repository
     * @return ServiceProvider[]
     */
    public static function getSpRequesterChain(
        ServiceProvider $serviceProvider,
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
     * @param ServiceProvider $serviceProvider
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param MetadataRepositoryInterface $repository
     * @return ServiceProvider
     */
    public static function getDestinationSpMetadata(
        ServiceProvider $serviceProvider,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        MetadataRepositoryInterface $repository
    ) {
        $requester = self::findRequesterServiceProvider($serviceProvider, $request, $repository);
        return $requester ? $requester : $serviceProvider;
    }

    /**
     * Get the metadata for a requester, if allowed by the configuration.
     *
     * @param ServiceProvider $serviceProvider
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param MetadataRepositoryInterface $repository
     * @return null|ServiceProvider
     */
    public static function findRequesterServiceProvider(
        ServiceProvider $serviceProvider,
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
