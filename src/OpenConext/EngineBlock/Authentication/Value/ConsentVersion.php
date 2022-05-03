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

namespace OpenConext\EngineBlock\Authentication\Value;

use OpenConext\EngineBlock\Assert\Assertion;

final class ConsentVersion
{
    const STABLE = 'stable';
    const UNSTABLE = 'unstable';
    const NOT_GIVEN = 'not-given';

    /**
     * @var string
     */
    private $consentVersion;

    public static function stable(): ConsentVersion
    {
        return new self(self::STABLE);
    }

    public static function unstable(): ConsentVersion
    {
        return new self(self::UNSTABLE);
    }

    public static function notGiven(): ConsentVersion
    {
        return new self(self::NOT_GIVEN);
    }

    public function __construct(string $consentVersion)
    {
        Assertion::choice(
            $consentVersion,
            [self::UNSTABLE, self::STABLE, self::NOT_GIVEN],
            'ConsentVersion must be one of ConsentVersion::STABLE, ConsentVersion::NOT_GIVEN or ConsentVersion::UNSTABLE'
        );

        $this->consentVersion = $consentVersion;
    }

    public function given(): bool
    {
        return $this->consentVersion !== self::NOT_GIVEN;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->consentVersion;
    }

    public function isUnstable(): bool
    {
        return $this->consentVersion === self::UNSTABLE;
    }
}
