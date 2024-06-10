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

use OpenConext\EngineBlock\Metadata\Factory\Decorator\EngineBlockServiceProviderMetadata;
use OpenConext\EngineBlock\Metadata\Factory\Factory\IdentityProviderFactory;
use OpenConext\EngineBlock\Metadata\Factory\Factory\ServiceProviderFactory;
use OpenConext\EngineBlock\Metadata\MetadataRepository\IdpsMetadataRepository;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use OpenConext\EngineBlockBundle\Exception\EntityCanNotBeFoundException;
use OpenConext\EngineBlock\Stepup\StepupEndpoint;

class MetadataProvider
{
    /**
     * @var MetadataRenderer
     */
    private $renderer;

    /**
     * @var ServiceProviderFactory
     */
    private $spFactory;

    /**
     * @var IdentityProviderFactory
     */
    private $idpFactory;

    /**
     * @var KeyPairFactory
     */
    private $keyPairFactory;

    /**
     * @var IdpsMetadataRepository
     */
    private $metadataRepository;

    public function __construct(
        MetadataRenderer $renderer,
        ServiceProviderFactory $spFactory,
        IdentityProviderFactory $idpFactory,
        KeyPairFactory $keyPairFactory,
        IdpsMetadataRepository $metadataRepository
    ) {
        $this->renderer = $renderer;
        $this->spFactory = $spFactory;
        $this->idpFactory = $idpFactory;
        $this->keyPairFactory = $keyPairFactory;
        $this->metadataRepository = $metadataRepository;
    }

    /**
     * Generate XML metadata for an SP
     *
     * @param string $keyId
     * @return string
     */
    public function metadataForSp(string $keyId): string
    {
        $serviceProvider = $this->spFactory->createEngineBlockEntityFrom($keyId);

        if ($serviceProvider) {
            return $this->renderer->fromServiceProviderEntity($serviceProvider, $keyId);
        }
        throw new EntityCanNotBeFoundException(sprintf('Unable to find the SP with entity ID "%s".', $serviceProvider->getEntityId()));
    }

    /**
     * Generate XML metadata for an IdP
     */
    public function metadataForIdp(?string $keyId): string
    {
        $identityProvider = $this->idpFactory->createEngineBlockEntityFrom($keyId);

        if ($identityProvider) {
            return $this->renderer->fromIdentityProviderEntity($identityProvider, $keyId);
        }
        throw new EntityCanNotBeFoundException(sprintf('Unable to find the SP with entity ID "%s".', $identityProvider->getEntityId()));
    }


    /**
     * Generate XML proxy metadata for the IdP's of an SP
     * This can be used to generate the WAYF
     *
     * The following steps are taken
     * 1. Load the EngineBlock IdP entity (used to override the certificates and contact persons)
     *    Using the appropriate filters and visitors.
     * 2. Load the IdPs (either based on 'allowedIdpEntityIds' of the specified IdP, or loading all.
     * 3. Render and sign the document
     */
    public function metadataForIdps(
        ?string $spEntityId,
        ?string $keyId
    ): string {

        if ($spEntityId) {
            // See if an sp-entity-id was specified for which we need to use sp specific metadata
            $spEntity = $this->metadataRepository->fetchServiceProviderByEntityId($spEntityId);
            if (!$spEntity->allowAll) {
                $identityProviders = $this->metadataRepository->findIdentityProvidersByEntityId($spEntity->allowedIdpEntityIds, $keyId);
            }
        }
        if (!isset($identityProviders)) {
            $identityProviders = $this->metadataRepository->findIdentityProviders($keyId);
        }

        // 3. Render and sign the document
        return $this->renderer->fromIdentityProviderEntities($identityProviders, $keyId);
    }


    /**
     * Generate XML metadata for the internal used stepup authentication SP
     *
     * @param string $keyId
     * @return string
     * @throws \EngineBlock_Exception
     */
    public function metadataForStepup(string $keyId): string
    {
        $serviceProvider = $this->spFactory->createStepupEntityFrom($keyId);

        return $this->renderer->fromServiceProviderEntity($serviceProvider, $keyId);
    }

    public function certificate(string $keyId): string
    {
        return $this->keyPairFactory->buildFromIdentifier($keyId)->getCertificate()->toPem();
    }
}
