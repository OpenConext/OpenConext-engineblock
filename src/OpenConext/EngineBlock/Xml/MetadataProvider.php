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

namespace OpenConext\EngineBlock\Xml;

use OpenConext\EngineBlock\Metadata\Factory\Factory\IdentityProviderFactory;
use OpenConext\EngineBlock\Metadata\Factory\Factory\ServiceProviderFactory;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlockBundle\Exception\EntityCanNotBeFoundException;
use OpenConext\EngineBlockBundle\Stepup\StepupEndpoint;

class MetadataProvider
{
    /**
     * @var MetadataRenderer
     */
    private $factory;

    /**
     * @var ServiceProviderFactory
     */
    private $spFactory;

    /**
     * @var IdentityProviderFactory
     */
    private $idpFactory;

    /**
     * @var MetadataRepositoryInterface
     */
    private $metadataRepository;

    /**
     * @var StepupEndpoint
     */
    private $stepupEndpoint;

    /**
     * @param MetadataRenderer $factory
     * @param ServiceProviderFactory $spFactory
     * @param IdentityProviderFactory $idpFactory
     * @param MetadataRepositoryInterface $metadataRepository
     * @param StepupEndpoint $stepupEndpoint
     */
    public function __construct(
        MetadataRenderer $factory,
        ServiceProviderFactory $spFactory,
        IdentityProviderFactory $idpFactory,
        MetadataRepositoryInterface $metadataRepository,
        StepupEndpoint $stepupEndpoint
    ) {
        $this->factory = $factory;
        $this->spFactory = $spFactory;
        $this->idpFactory = $idpFactory;
        $this->metadataRepository = $metadataRepository;
        $this->stepupEndpoint = $stepupEndpoint;
    }

    /**
     * Generate XML metadata for an SP
     *
     * @param string $entityId
     * @param string $acsLocation
     * @param string $keyId
     * @return string
     */
    public function metadataForSp(string $entityId, string $acsLocation, string $keyId): string
    {
        $serviceProvider = $this->spFactory->createEngineBlockEntityFrom($entityId, $acsLocation, $keyId);

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
        $identityProvider = $this->idpFactory->createEngineBlockEntityFrom($entityId, $ssoLocation, $keyId);

        if ($identityProvider) {
            return $this->factory->fromIdentityProviderEntity($identityProvider, $keyId);
        }
        throw new EntityCanNotBeFoundException(sprintf('Unable to find the SP with entity ID "%s".', $entityId));
    }


    /**
     * Generate XML proxy metadata for the IdP's of an SP
     * This can be used to generate the WAYF
     *
     * @param string $entityId
     * @param string $keyId
     * @param string|null $serviceProviderEntityId
     * @return string
     */
    public function metadataForIdpsOfSp(string $entityId, string $keyId, string $serviceProviderEntityId = null): string
    {
        $identityProviders = $this->metadataRepository->findIdentityProviders();

        // Todo: implement sp filter sp-entity-id to list only allowed idps
        // Todo: hide on coin hidden

        return $this->factory->fromIdentityProviderEntities($identityProviders, $keyId);
    }


    /**
     * Generate XML metadata for the internal used stepup authentication SP
     *
     * @param string $acsLocation
     * @param string $keyId
     * @return string
     * @throws \EngineBlock_Exception
     */
    public function metadataForStepup(string $acsLocation, string $keyId): string
    {
        $serviceProvider = $this->spFactory->createMinimalEntity(
            $this->stepupEndpoint->getEntityId(),
            $acsLocation,
            $keyId
        );

        return $this->factory->fromServiceProviderEntity($serviceProvider, $keyId);
    }
}
