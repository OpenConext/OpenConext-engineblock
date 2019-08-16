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

namespace OpenConext\EngineBlockBundle\Stepup;

use EngineBlock_X509_CertificateFactory;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Service;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;

class StepupIdentityProvider extends ServiceProvider
{

    /**
     * @param StepupEndpoint $stepupEndpoint
     * @param string $acsLocation
     * @return IdentityProvider
     * @throws \EngineBlock_Exception
     */
    public static function fromStepupEndpoint(StepupEndpoint $stepupEndpoint, $acsLocation)
    {
        $entity = new IdentityProvider($stepupEndpoint->getEntityId());

        $entity->responseProcessingService = new Service(
            $acsLocation,
            Constants::BINDING_HTTP_POST
        );

        $publicKeyFactory = new EngineBlock_X509_CertificateFactory();

        $entity->certificates[] = $publicKeyFactory->fromFile($stepupEndpoint->getKeyFile());
        $entity->singleSignOnServices[] = new Service($stepupEndpoint->getSsoLocation(), Constants::BINDING_HTTP_POST);
        $entity->requestsMustBeSigned = true;
        $entity->signatureMethod = XMLSecurityKey::RSA_SHA256;

        return $entity;
    }
}
