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

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\Loa;
use OpenConext\EngineBlock\Metadata\LoaRepository;

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
        StepupEndpoint $stepupEndpoint,
        LoaRepository $loaRepository
    ) {
        $this->gatewayLoaMapping = $gatewayLoaMapping;
        $this->stepupEndpoint = $stepupEndpoint;
        $this->loaRepository = $loaRepository;
    }

    /**
     * @param IdentityProvider $identityProvider
     * @param ServiceProvider $serviceProvider
     * @param array $pdpLoas
     * @return bool
     */
    public function shouldUseStepup(
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider,
        array $pdpLoas
    ) {
        $stepupDecision = new StepupDecision($identityProvider, $serviceProvider, $pdpLoas, $this->loaRepository);
        return $stepupDecision->shouldUseStepup();
    }

    /**
     * @param IdentityProvider $identityProvider
     * @param ServiceProvider $serviceProvider
     * @param array $pdpLoas
     * @return string|null
     */
    public function getStepupLoa(
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider,
        array $pdpLoas
    ) {
        $stepupDecision = new StepupDecision($identityProvider, $serviceProvider, $pdpLoas, $this->loaRepository);
        return $this->gatewayLoaMapping->transformToGatewayLoa($stepupDecision->getStepupLoa());
    }

    /**
     * @return Loa
     */
    public function getStepupLoa1()
    {
        return $this->gatewayLoaMapping->getGatewayLoa1();
    }

    /**
     * @param string $gatewayLoa
     * @return Loa
     */
    public function getEbLoa($gatewayLoa)
    {
        Assertion::nonEmptyString(
            $gatewayLoa,
            sprintf('The gatewayLoa should be a non empty string. "%s" was provided', $gatewayLoa)
        );
        $loa = $this->loaRepository->getByIdentifier($gatewayLoa);
        return $this->gatewayLoaMapping->transformToEbLoa($loa);
    }

    /**
     * @param IdentityProvider $identityProvider
     * @param ServiceProvider $serviceProvider
     * @return bool
     */
    public function allowNoToken(IdentityProvider $identityProvider, ServiceProvider $serviceProvider)
    {
        $stepupDecision = new StepupDecision($identityProvider, $serviceProvider, [], $this->loaRepository);
        return $stepupDecision->allowNoToken();
    }
}
