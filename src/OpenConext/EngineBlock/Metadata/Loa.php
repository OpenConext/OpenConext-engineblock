<?php declare(strict_types=1);

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
    const LOA_1 = 10;
    const LOA_1_5 = 15;
    const LOA_2 = 20;
    const LOA_3 = 30;

    /**
     * @var int
     */
    private $level;

    /**
     * @var string
     */
    private $identifier;

    public static function create(int $level, string $identifier)
    {
        $possibleLevels = [self::LOA_1, self::LOA_1_5, self::LOA_2, self::LOA_3];
        Assertion::inArray(
            $level,
            $possibleLevels,
            sprintf('Please provide a valid level. Acceptable LoA levels are "%s"', implode(', ', $possibleLevels))
        );
        Assertion::nonEmptyString($identifier, 'The LoA identifier must be of type string, and can not be empty');

        $loa = new self;

        $loa->level = $level;
        $loa->identifier = $identifier;

        return $loa;
    }

    public function levelIsHigherOrEqualTo(Loa $loa): bool
    {
        $isHigherOrEqualTo = $this->level >= $loa->getLevel();
        return $isHigherOrEqualTo;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
