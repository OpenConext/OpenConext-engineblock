<?php
declare(strict_types=1);

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

use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Service;
use OpenConext\EngineBlock\Metadata\X509\KeyPairFactory;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;

class MetadataEntityFactory
{
    /**
     * @var KeyPairFactory
     */
    private $keyPairFactory;

    public function __construct(KeyPairFactory $keyPairFactory)
    {
        $this->keyPairFactory = $keyPairFactory;
    }

    /**
     * @param string $entityId
     * @param string $acsLocation
     * @param string $keyId
     * @return ServiceProvider
     */
    public function metadataSpFrom(string $entityId, string $acsLocation, string $keyId)
    {
        $keyPair = $this->keyPairFactory->buildFromIdentifier($keyId);
        $certificates[] = $keyPair->getCertificate();

        $assertionConsumerServices[] = new IndexedService(
            $acsLocation,
            Constants::BINDING_HTTP_POST,
            0
        );

        $entity = new ServiceProvider(
            $entityId,
            null,
            null,
            null,
            false,
            $certificates,
            [],
            '',
            '',
            false,
            '',
            '',
            '',
            '',
            null,
            '',
            '',
            null,
            [],
            null,
            false,
            true,
            XMLSecurityKey::RSA_SHA256,
            null,
            IdentityProvider::WORKFLOW_STATE_DEFAULT,
            [],
            false,
            $assertionConsumerServices,
            IdentityProvider::GUEST_QUALIFIER_ALL,
            true,
            null,
            [],
            [],
            null,
            false,
            false,
            false,
            true,
            '',
            null,
            null,
            null,
            null,
            null
        );

        return $entity;
    }



    /**
     * @param string $entityId
     * @param string $ssoLocation
     * @param string $keyId
     * @return IdentityProvider
     */
    public function metadataIdpFrom(string $entityId, string $ssoLocation, string $keyId)
    {
        $keyPair = $this->keyPairFactory->buildFromIdentifier($keyId);
        $certificates[] = $keyPair->getCertificate();

        $singleSignOnServices[] = new Service($ssoLocation, Constants::BINDING_HTTP_REDIRECT);

        $entity = new IdentityProvider(
            $entityId,
            null,
            null,
            null,
            false,
            $certificates,
            [],
            '',
            '',
            false,
            '',
            '',
            '',
            '',
            null,
            '',
            '',
            null,
            [],
            null,
            false,
            true,
            XMLSecurityKey::RSA_SHA256,
            null,
            IdentityProvider::WORKFLOW_STATE_DEFAULT,
            '',
            null,
            false,
            IdentityProvider::GUEST_QUALIFIER_ALL,
            true,
            null,
            [],
            $singleSignOnServices,
            null,
            null
        );

        return $entity;
    }
}
