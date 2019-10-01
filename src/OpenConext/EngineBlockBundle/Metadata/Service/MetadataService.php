<?php declare(strict_types=1);

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

namespace OpenConext\EngineBlockBundle\Metadata\Service;

use OpenConext\EngineBlockBundle\Exception\EntityCanNotBeFoundException;
use OpenConext\EngineBlockBundle\Metadata\MetadataEntityFactory;
use OpenConext\EngineBlockBundle\Metadata\MetadataFactory;

class MetadataService
{
    /**
     * @var MetadataFactory
     */
    private $factory;
    /**
     * @var MetadataEntityFactory
     */
    private $metadataEntityFactory;

    /**
     * @param MetadataFactory $factory
     * @param MetadataEntityFactory $metadataEntityFactory
     */
    public function __construct(MetadataFactory $factory, MetadataEntityFactory $metadataEntityFactory)
    {
        $this->factory = $factory;
        $this->metadataEntityFactory = $metadataEntityFactory;
    }

    /**
     * Generate XML metadata for a SP
     *
     * @param string $entityId
     * @param string $acsLocation
     * @param string $keyId
     * @return string
     */
    public function metadataForSp(string $entityId, string $acsLocation, string $keyId): string
    {
        $serviceProvider = $this->metadataEntityFactory->metadataSpFrom($entityId, $acsLocation, $keyId);

        if ($serviceProvider) {
            return $this->factory->fromServiceProviderEntity($serviceProvider, $keyId);
        }
        throw new EntityCanNotBeFoundException(sprintf('Unable to find the SP with entity ID "%s".', $entityId));
    }


    /**
     * Generate XML metadata for an IdP
     *
     * @param string $entityId
     * @param string $ssoLocation
     * @param string $keyId
     * @return string
     */
    public function metadataForIdp(string $entityId, string $ssoLocation, string $keyId): string
    {
        $identityProvider = $this->metadataEntityFactory->metadataIdpFrom($entityId, $ssoLocation, $keyId);

        if ($identityProvider) {
            return $this->factory->fromIdentityProviderEntity($identityProvider, $keyId);
        }
        throw new EntityCanNotBeFoundException(sprintf('Unable to find the SP with entity ID "%s".', $entityId));
    }
}
