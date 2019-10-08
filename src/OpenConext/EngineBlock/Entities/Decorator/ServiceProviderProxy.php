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

namespace OpenConext\EngineBlock\Entities\Decorator;

use EngineBlock_Attributes_Metadata as AttributesMetadata;
use OpenConext\EngineBlock\Entities\ServiceProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use SAML2\Constants;

class ServiceProviderProxy extends AbstractServiceProvider
{
    /**
     * @var X509KeyPair
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

    public function __construct(
        ServiceProviderEntityInterface $entity,
        X509KeyPair $keyPair,
        AttributesMetadata $attributes,
        Service $consentService
    ) {
        parent::__construct($entity);

        $this->keyPair = $keyPair;
        $this->attributes = $attributes;
        $this->consentService = $consentService;
    }


    public function getCertificates(): array
    {
        return [$this->keyPair->getCertificate()];
    }

    public function getSupportedNameIdFormats(): array
    {
        return [
            Constants::NAMEID_PERSISTENT,
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_UNSPECIFIED,
        ];
    }

    public function getRequestedAttributes(): ?array
    {
        $attributes = [];
        foreach ($this->attributes->findRequestedAttributeIds() as $attributeId) {
            $attributes[] = new RequestedAttribute($attributeId);
        }

        foreach ($this->attributes->findRequiredAttributeIds() as $attributeId) {
            $attributes[] = new RequestedAttribute($attributeId, true);
        }

        return $attributes;
    }

    public function getResponseProcessingService(): string
    {
        return $this->consentService;
    }

    public function isAllowAll(): bool
    {
        return true;
    }
}
