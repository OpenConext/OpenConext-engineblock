<?php

/**
 * Copyright 2016 SURFnet B.V.
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

use EngineBlock_Saml2_IdGenerator;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use SAML2\AuthnRequest;
use SAML2\Constants;

final class SfoGatewayCallOutHelper
{
    /**
     * @var SfoGatewayLoaMapping
     */
    private $gatewayLoaMapping;

    /**
     * @var SfoEndpoint
     */
    private $sfoEndpoint;

    public function __construct(
        SfoGatewayLoaMapping $gatewayLoaMapping,
        SfoEndpoint $sfoEndpoint
    ) {
        $this->gatewayLoaMapping = $gatewayLoaMapping;
        $this->sfoEndpoint = $sfoEndpoint;
    }

    /**
     * @param IdentityProvider $identityProvider
     * @param ServiceProvider $serviceProvider
     * @return bool
     */
    public function shouldUseSfo(IdentityProvider $identityProvider, ServiceProvider $serviceProvider)
    {
        $sfoDecision = new SfoDecision($identityProvider, $serviceProvider);
        return $sfoDecision->shouldUseSfo();
    }

    /**
     * @param IdentityProvider $identityProvider
     * @param ServiceProvider $serviceProvider
     * @return string|null
     */
    public function getSfoLoa(IdentityProvider $identityProvider, ServiceProvider $serviceProvider)
    {
        $sfoDecision = new SfoDecision($identityProvider, $serviceProvider);
        return $this->gatewayLoaMapping->transformToGatewayLoa($sfoDecision->getSfoLoa());
    }

    /**
     * @param IdentityProvider $identityProvider
     * @param ServiceProvider $serviceProvider
     * @return bool
     */
    public function allowNoToken(IdentityProvider $identityProvider, ServiceProvider $serviceProvider)
    {
        $sfoDecision = new SfoDecision($identityProvider, $serviceProvider);
        return $sfoDecision->allowNoToken();
    }
}
