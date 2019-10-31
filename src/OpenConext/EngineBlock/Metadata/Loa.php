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

/**
 * Value object representing the different LoAs that can be configured
 */
class Loa
{
    /**
     * The different levels
     */
    const LOA_1 = 1;
    const LOA_2 = 2;
    const LOA_3 = 3;

    /**
     * @var int
     */
    private $level;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @param int $level
     * @param string $identifier
     */
    public static function create($level, $identifier)
    {
        $possibleLevels = [self::LOA_1, self::LOA_2, self::LOA_3];

        Assertion::integer($level, 'The LoA level must be an integer value');
        Assertion::inArray(
            $level,
            $possibleLevels,
            sprintf('Please provide a valid level. Accpetable LoA levels are "%s"', implode(', ', $possibleLevels))
        );
        Assertion::nonEmptyString($identifier, 'The LoA identifier must be of type string, and can not be empty');

        $loa = new self;

        $loa->level = $level;
        $loa->identifier = $identifier;

        return $loa;
    }

    /**
     * @param int $level
     * @return bool
     */
    public function levelIsHigherOrEqualTo($level)
    {
        Assertion::integer($level, 'Provide the integer value representing the LoA level');
        Assertion::greaterThan($level, 0, 'Please provide a positive integer value');
        $isHigherOrEqualTo = $this->level >= $level;
        return $isHigherOrEqualTo;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function __toString()
    {
        return $this->identifier;
    }
}
