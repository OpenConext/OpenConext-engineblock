<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;

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
            return $carry | $entity->getCoins()->additionalLogging();
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
     * @param \Psr\Log\LoggerInterface $logger
     * @return null|ServiceProvider
     * @throws EngineBlock_Exception_UnknownServiceProvider
     */
    public static function findRequesterServiceProvider(
        ServiceProvider $serviceProvider,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        MetadataRepositoryInterface $repository,
        \Psr\Log\LoggerInterface $logger = null
    ) {
        if (!$serviceProvider->getCoins()->isTrustedProxy()) {
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
            if ($serviceProvider->getCoins()->requesteridRequired()) {
                throw new EngineBlock_Exception_UnknownServiceProvider(
                    $serviceProvider,
                    'No RequesterID specified'
                );
            }
            return null;
        }

        // Find and validate the lastRequesterEntity, the Entity is filtered against the implementations of the
        // MetadataRepository/Filter/FilterInterface. Any of these filters can reject the entity as being a valid SP.
        // The filters will log any additional information that is available.
        $lastRequesterEntity = $repository->findServiceProviderByEntityId($lastRequesterEntityId, $logger);
        if (!$lastRequesterEntity) {
            throw new EngineBlock_Exception_UnknownServiceProvider(
                $serviceProvider,
                $lastRequesterEntityId
            );
        }

        return $lastRequesterEntity;
    }
}
