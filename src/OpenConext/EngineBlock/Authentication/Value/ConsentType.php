<?php

/**
 * Copyright 2014 SURFnet B.V.
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

namespace OpenConext\EngineBlock\Authentication\Value;

use JsonSerializable;
use OpenConext\EngineBlock\Assert\Assertion;

final class ConsentType implements JsonSerializable
{
    const TYPE_EXPLICIT = 'explicit';
    const TYPE_IMPLICIT = 'implicit';

    /**
     * @var string
     */
    private $consentType;

    /**
     * @return ConsentType
     */
    public static function explicit()
    {
        return new self(self::TYPE_EXPLICIT);
    }

    /**
     * @return ConsentType
     */
    public static function implicit()
    {
        return new self(self::TYPE_IMPLICIT);
    }

    /**
     * @param ConsentType::TYPE_EXPLICIT|ConsentType::TYPE_IMPLICIT $consentType
     *
     * @deprecated Use the implicit and explicit named constructors. Will be removed
     *             when Doctrine ORM is implemented.
     */
    public function __construct($consentType)
    {
        Assertion::choice(
            $consentType,
            [self::TYPE_EXPLICIT, self::TYPE_IMPLICIT],
            'ConsentType must be one of ConsentType::TYPE_EXPLICIT, ConsentType::TYPE_IMPLICIT'
        );

        $this->consentType = $consentType;
    }

    /**
     * @param ConsentType $other
     * @return bool
     */
    public function equals(ConsentType $other)
    {
        return $this->consentType === $other->consentType;
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->consentType;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->consentType;
    }
}
