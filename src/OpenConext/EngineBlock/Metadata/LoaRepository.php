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

namespace OpenConext\EngineBlock\Metadata;

use OpenConext\EngineBlock\Assert\Assertion;
use OpenConext\EngineBlock\Exception\LoaNotFoundException;
use function array_key_exists;

/**
 * Retrieve LoA value objects from the configured LoA mapping
 */
class LoaRepository
{
    private const EB = 'eb';
    private const GW = 'gw';
    /**
     * @var array
     */
    private $store = [];

    /**
     * @param array $loaMapping
     */
    public function __construct(array $loaMapping)
    {
        foreach ($loaMapping as $level => $mapping) {
            Assertion::integer(
                $level,
                'The stepup.loa.mapping should be followed by an integer value, indicating the LoA level. ' .
                'Example: stepup.loa.mapping.3'
            );
            Assertion::keysExist(
                $mapping,
                ['engineblock', 'gateway'],
                'Both the engineblock and gateway keys must be present in every LoA mapping.'
            );
            Assertion::string($mapping['engineblock'], 'The EngineBlock LoA must be a string value');
            Assertion::string($mapping['gateway'], 'The Gateway LoA must be a string value');

            $this->store[self::EB][$mapping['engineblock']] = Loa::create($level, $mapping['engineblock']);
            $this->store[self::GW][$mapping['gateway']] = Loa::create($level, $mapping['gateway']);
        }
    }

    /**
     * @param $identifier
     * @return Loa
     * @throws LoaNotFoundException
     */
    public function getByIdentifier($identifier)
    {
        if (!array_key_exists($identifier, $this->store[self::EB]) &&
            !array_key_exists($identifier, $this->store[self::GW])
        ) {
            throw new LoaNotFoundException(sprintf('Unable to find LoA with identifier "%s"', $identifier));
        }
        if (array_key_exists($identifier, $this->store[self::EB])) {
            return $this->store[self::EB][$identifier];
        }
        return $this->store[self::GW][$identifier];
    }

    /**
     * @return Loa[]
     */
    public function getStepUpLoas()
    {
        return $this->store[self::EB];
    }
}
