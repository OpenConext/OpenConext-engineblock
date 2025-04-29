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
use Psr\Log\LoggerInterface;

final class StepupGatewayCallOutHelper
{
    /**
     * @var StepupGatewayLoaMapping
     */
    private $gatewayLoaMapping;

    private $logger;

    /**
     * @var LoaRepository
     */
    private $loaRepository;

    public function __construct(
        StepupGatewayLoaMapping $gatewayLoaMapping,
        LoaRepository $loaRepository,
        LoggerInterface $logger
    ) {
        $this->gatewayLoaMapping = $gatewayLoaMapping;
        $this->loaRepository = $loaRepository;
        $this->logger = $logger;
    }

    public function shouldUseStepup(
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider,
        array $authnRequestLoas,
        array $pdpLoas
    ) : bool {
        $stepupDecision = new StepupDecision(
            $identityProvider,
            $serviceProvider,
            $authnRequestLoas,
            $pdpLoas,
            $this->loaRepository,
            $this->logger
        );
        return $stepupDecision->shouldUseStepup();
    }

    public function getStepupLoa(
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider,
        array $authnRequestLoas,
        array $pdpLoas
    ) : ?Loa {
        $stepupDecision = new StepupDecision(
            $identityProvider,
            $serviceProvider,
            $authnRequestLoas,
            $pdpLoas,
            $this->loaRepository,
            $this->logger
        );
        return $this->gatewayLoaMapping->transformToGatewayLoa($stepupDecision->getStepupLoa());
    }

    public function getStepupLoa1() : Loa
    {
        return $this->gatewayLoaMapping->getGatewayLoa1();
    }

    public function getEbLoa(string $gatewayLoa) : Loa
    {
        Assertion::nonEmptyString(
            $gatewayLoa,
            sprintf('The gatewayLoa should be a non empty string. "%s" was provided', $gatewayLoa)
        );
        $loa = $this->loaRepository->getByIdentifier($gatewayLoa);
        return $this->gatewayLoaMapping->transformToEbLoa($loa);
    }

    public function allowNoToken(IdentityProvider $identityProvider, ServiceProvider $serviceProvider) : bool
    {
        $stepupDecision = new StepupDecision($identityProvider, $serviceProvider, [], [], $this->loaRepository, $this->logger);
        return $stepupDecision->allowNoToken();
    }
}
