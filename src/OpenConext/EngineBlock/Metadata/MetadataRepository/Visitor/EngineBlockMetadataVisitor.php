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

namespace OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor;

use EngineBlock_X509_KeyPair as KeyPair;
use EngineBlock_Attributes_Metadata as AttributesMetadata;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use OpenConext\EngineBlock\Metadata\Service;
use SAML2\Constants;

/**
 * @package OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor
 */
class EngineBlockMetadataVisitor implements VisitorInterface
{
    /**
     * @var string
     */
    private $idpEntityId;

    /**
     * @var string
     */
    private $spEntityId;

    /**
     * @var KeyPair
     */
    private $keyPair;

    /**
     * @var AttributesMetadata
     */
    private $attributes;

    /**
     * @var Service
     */
    private $consentService;

    /**
     * @param string $idpEntityId
     * @param string $spEntityId
     * @param KeyPair $keyPair
     * @param AttributesMetadata $attributes
     * @param Service $consentService
     */
    public function __construct(
        $idpEntityId,
        $spEntityId,
        KeyPair $keyPair,
        AttributesMetadata $attributes,
        Service $consentService
    ) {
        $this->idpEntityId = $idpEntityId;
        $this->spEntityId = $spEntityId;
        $this->keyPair = $keyPair;
        $this->attributes = $attributes;
        $this->consentService = $consentService;
    }

    /**
     * {@inheritdoc}
     */
    public function visitIdentityProvider(IdentityProvider $entity)
    {
        if ($entity->entityId === $this->idpEntityId) {
            $this->setCertificate($entity);
            $this->setNameIdFormats($entity);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function visitServiceProvider(ServiceProvider $entity)
    {
        if ($entity->entityId === $this->spEntityId) {
            $this->setCertificate($entity);
            $this->setNameIdFormats($entity);
            $this->setConsentService($entity);
            $this->setRequestedAttributes($entity);
        }
    }

    /**
     * Override certificate data.
     *
     * EngineBlock does not use the certificate configured in the metadata
     * source (service registry / manage), but uses the certificate configured
     * in application config instead. This method overrides the certificate in
     * order for the metadata pages (exposed trough the welcome page) to show
     * the correct certificate.
     *
     * @param AbstractRole $entity
     */
    private function setCertificate(AbstractRole $entity)
    {
        $entity->certificates = array($this->keyPair->getCertificate());
    }

    /**
     * Override nameID formats.
     *
     * EngineBlock supports persistent, transient and unspecified nameID
     * formats. For unknown historical reasons we don't trust the metadata
     * source (service registry / manage) to list all the supported formats so
     * we explicitly override it here.
     *
     * @param AbstractRole $entity
     */
    private function setNameIdFormats(AbstractRole $entity)
    {
        $entity->supportedNameIdFormats = array(
            Constants::NAMEID_PERSISTENT,
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_UNSPECIFIED,
        );
    }

    /**
     * The attributes required by EB SP are application configuration, and not
     * configured with ARP in service registry / manage. We read the attribute
     * configuration (attributes-x.x.x.json) and add all optional and
     * non-optional attributes we know about as 'requested'.
     *
     * It's unclear if this is needed for anything other than the consent
     * page.
     *
     * @param ServiceProvider $entity
     */
    private function setRequestedAttributes(ServiceProvider $entity)
    {
        foreach ($this->attributes->findRequestedAttributeIds() as $attributeId) {
            $entity->requestedAttributes[] = new RequestedAttribute($attributeId);
        }

        foreach ($this->attributes->findRequiredAttributeIds() as $attributeId) {
            $entity->requestedAttributes[] = new RequestedAttribute($attributeId, true);
        }
    }

    /**
     * Configure the consent service.
     *
     * The consent service is implemented using a so-called 'internal
     * binding'. This can be considered an implementation detail, and can not
     * and should not be configured in service registry / manage. This method
     * overrides the metadata so the internal consent service is used.
     *
     * It's unclear if the 'allow all entity ids' is still required for the
     * consent service to function.
     *
     * @param ServiceProvider $entity
     */
    private function setConsentService(ServiceProvider $entity)
    {
        $entity->responseProcessingService = $this->consentService;
        $entity->allowAll = true;
    }
}
