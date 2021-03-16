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
use OpenConext\EngineBlock\Exception\RuntimeException;
use OpenConext\EngineBlock\Metadata\Loa;
use OpenConext\EngineBlock\Metadata\LoaRepository;

class StepupGatewayLoaMapping
{
    private $gatewayToEngine = [];
    private $engineToGateway = [];
    private $gatewayLoa1 = '';

    /**
     * @throws \Assert\AssertionFailedException
     */
    public function __construct(array $loaMapping, string $gatewayLoa1, LoaRepository $loaRepository)
    {
        Assertion::string($gatewayLoa1, 'The stepup.loa.loa1 configuration must be a string');
        $this->gatewayLoa1 = $loaRepository->getByIdentifier($gatewayLoa1);

        foreach ($loaMapping as $mapping) {
            Assertion::nonEmptyString(
                $mapping['gateway'],
                sprintf('The gateway LoA must be a non empty string. "%s" given', $mapping['gateway'])
            );
            Assertion::nonEmptyString(
                $mapping['engineblock'],
                sprintf('The engineblock LoA must be a non empty string. "%s" given', $mapping['engineblock'])
            );

            $gwLoa = $loaRepository->getByIdentifier($mapping['gateway']);
            $ebLoa = $loaRepository->getByIdentifier($mapping['engineblock']);
            Assertion::keyNotExists(
                $this->gatewayToEngine,
                $gwLoa->getIdentifier(),
                'Found a duplicate Gateway LoA identifier, this is not allowed.'
            );
            Assertion::keyNotExists(
                $this->engineToGateway,
                $ebLoa->getIdentifier(),
                'Found a duplicate EngineBlock LoA identifier, this is not allowed.'
            );
            $this->gatewayToEngine[$gwLoa->getIdentifier()] = $ebLoa;
            $this->engineToGateway[$ebLoa->getIdentifier()] = $gwLoa;
        }
    }

    public function transformToGatewayLoa(Loa $engineBlockLoa) : Loa
    {
        if (!array_key_exists($engineBlockLoa->getIdentifier(), $this->engineToGateway)) {
            throw new RuntimeException('Unable to find the EngineBlock LoA in the configured stepup LoA mapping');
        }
        return $this->engineToGateway[$engineBlockLoa->getIdentifier()];
    }

    public function transformToEbLoa(Loa $gatewayLoa) : Loa
    {
        if (!array_key_exists($gatewayLoa->getIdentifier(), $this->gatewayToEngine)) {
            throw new RuntimeException('Unable to find the received stepup LoA in the configured EngineBlock LoA mapping');
        }
        return $this->gatewayToEngine[$gatewayLoa->getIdentifier()];
    }

    public function getGatewayLoa1() : Loa
    {
        return $this->gatewayLoa1;
    }
}
