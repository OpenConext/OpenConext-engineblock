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

use EngineBlock_Saml2_IdGenerator;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use SAML2\AuthnRequest;
use SAML2\Constants;

final class StepupGatewayCallOutHelper
{
    /**
     * @var StepupGatewayLoaMapping
     */
    private $gatewayLoaMapping;

    /**
     * @var StepupEndpoint
     */
    private $stepupEndpoint;

    public function __construct(
        StepupGatewayLoaMapping $gatewayLoaMapping,
        StepupEndpoint $stepupEndpoint
    ) {
        $this->gatewayLoaMapping = $gatewayLoaMapping;
        $this->stepupEndpoint = $stepupEndpoint;
    }

    /**
     * @param IdentityProvider $identityProvider
     * @param ServiceProvider $serviceProvider
     * @return bool
     */
    public function shouldUseStepup(IdentityProvider $identityProvider, ServiceProvider $serviceProvider)
    {
        $stepupDecision = new StepupDecision($identityProvider, $serviceProvider);
        return $stepupDecision->shouldUseStepup();
    }

    /**
     * @param IdentityProvider $identityProvider
     * @param ServiceProvider $serviceProvider
     * @return string|null
     */
    public function getStepupLoa(IdentityProvider $identityProvider, ServiceProvider $serviceProvider)
    {
        $stepupDecision = new StepupDecision($identityProvider, $serviceProvider);
        return $this->gatewayLoaMapping->transformToGatewayLoa($stepupDecision->getStepupLoa());
    }

    /**
     * @return string
     */
    public function getStepupLoa1()
    {
        return $this->gatewayLoaMapping->getGatewayLoa1();
    }

    /**
     * @param IdentityProvider $identityProvider
     * @param ServiceProvider $serviceProvider
     * @return bool
     */
    public function allowNoToken(IdentityProvider $identityProvider, ServiceProvider $serviceProvider)
    {
        $stepupDecision = new StepupDecision($identityProvider, $serviceProvider);
        return $stepupDecision->allowNoToken();
    }
}
