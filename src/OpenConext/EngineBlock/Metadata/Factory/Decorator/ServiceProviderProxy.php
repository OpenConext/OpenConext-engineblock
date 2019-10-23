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

namespace OpenConext\EngineBlock\Metadata\Factory\Decorator;

use EngineBlock_Attributes_Metadata as AttributesMetadata;
use OpenConext\EngineBlock\Metadata\Factory\ServiceProviderEntityInterface;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\X509KeyPair;
use OpenConext\EngineBlockBundle\Url\UrlProvider;
use SAML2\Constants;

/**
 * This decoration is used to represent EngineBlock in it's SP role when EngineBlock is used as authentication
 * proxy. It will make sure all required parameters to support EB are set.
 */
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
     * @var UrlProvider
     */
    private $urlProvider;

    public function __construct(
        ServiceProviderEntityInterface $entity,
        X509KeyPair $keyPair,
        AttributesMetadata $attributes,
        UrlProvider $urlProvider
    ) {
        parent::__construct($entity);

        $this->keyPair = $keyPair;
        $this->attributes = $attributes;
        $this->urlProvider = $urlProvider;
    }


    public function getCertificates(): array
    {
        return [$this->keyPair->getCertificate()];
    }

    /**
     * @return string[]
     */
    public function getSupportedNameIdFormats(): array
    {
        return [
            Constants::NAMEID_PERSISTENT,
            Constants::NAMEID_TRANSIENT,
            Constants::NAMEID_UNSPECIFIED,
        ];
    }

    /**
     * @return RequestedAttribute[]|null
     */
    public function getRequestedAttributes(): ?array
    {
        return $this->attributes->getRequestedAttributes();
    }

    public function isAllowAll(): bool
    {
        return true;
    }

    public function isAllowed(string $idpEntityId): bool
    {
        return true;
    }

    /**
     * @return IndexedService[]
     */
    public function getAssertionConsumerServices(): array
    {
        $acsLocation = $this->urlProvider->getUrl('authentication_sp_consume_assertion', false, null, null);
        return [new IndexedService($acsLocation, Constants::BINDING_HTTP_POST, 0)];
    }
}
