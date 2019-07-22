<?php
/**
 * Copyright 2019 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Sfo;

use EngineBlock_X509_CertificateFactory;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\IndexedService;
use SAML2\Constants;

class SfoServiceProvider extends ServiceProvider
{

    /**
     * @param SfoEndpoint $sfoEndpoint
     * @return ServiceProvider
     */
    public static function fromSfoEndpoint(SfoEndpoint $sfoEndpoint)
    {
        $entity = new ServiceProvider($sfoEndpoint->getEntityId());

        $entity->assertionConsumerServices[] = new IndexedService(
            $sfoEndpoint->getSsoLocation(),
            Constants::BINDING_HTTP_POST,
            0
        );

        $publicKeyFactory = new EngineBlock_X509_CertificateFactory();

        $entity->certificates[] = $publicKeyFactory->fromFile($sfoEndpoint->getKeyFile());

        return $entity;
    }
}
