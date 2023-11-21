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

namespace OpenConext\EngineBlock\Stepup;

use EngineBlock_X509_CertificateFactory;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\IndexedService;
use OpenConext\EngineBlock\Metadata\Mdui;
use OpenConext\EngineBlock\Metadata\Service;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;

class StepupEntityFactory
{
    /**
     * @throws \EngineBlock_Exception
     */
    public static function idpFrom(StepupEndpoint $stepupEndpoint, ?string $acsLocation) : IdentityProvider
    {
        $certificates = $singleSignOnServices = [];
        $publicKeyFactory = new EngineBlock_X509_CertificateFactory();
        $certificates[] = $publicKeyFactory->fromFile($stepupEndpoint->getKeyFile());
        $singleSignOnServices[] = new Service($stepupEndpoint->getSsoLocation(), Constants::BINDING_HTTP_REDIRECT);

        $entity = new IdentityProvider(
            $stepupEndpoint->getEntityId(),
            Mdui::emptyMdui(),
            null,
            null,
            null,
            null,
            false,
            $certificates,
            [],
            '',
            '',
            '',
            false,
            '',
            '',
            '',
            '',
            '',
            '',
            null,
            '',
            '',
            '',
            null,
            array(
                Constants::NAMEID_TRANSIENT,
                Constants::NAMEID_PERSISTENT,
            ),
            true,
            XMLSecurityKey::RSA_SHA256,
            IdentityProvider::WORKFLOW_STATE_DEFAULT,
            '',
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


    /**
     * @throws \EngineBlock_Exception
     */
    public static function spFrom(StepupEndpoint $stepupEndpoint, ?string $acsLocation) : ServiceProvider
    {
        $certificates = $assertionConsumerServices = [];
        $publicKeyFactory = new EngineBlock_X509_CertificateFactory();
        $certificates[] = $publicKeyFactory->fromFile($stepupEndpoint->getKeyFile());
        $assertionConsumerServices[] = new IndexedService(
            $acsLocation,
            Constants::BINDING_HTTP_POST,
            0
        );

        $entity = new ServiceProvider(
            $stepupEndpoint->getEntityId(),
            null,
            null,
            null,
            null,
            false,
            $certificates,
            [],
            '',
            '',
            '',
            false,
            '',
            '',
            '',
            '',
            '',
            '',
            null,
            '',
            '',
            '',
            null,
            array(
                Constants::NAMEID_TRANSIENT,
                Constants::NAMEID_PERSISTENT,
            ),
            true,
            XMLSecurityKey::RSA_SHA256,
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
            null,
            null
        );

        return $entity;
    }
}
