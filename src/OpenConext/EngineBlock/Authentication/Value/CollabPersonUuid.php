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
use Ramsey\Uuid\Uuid;

final class CollabPersonUuid
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @return CollabPersonUuid
     */
    public static function generate()
    {
        return new self((string) Uuid::uuid4());
    }

    /**
     * @param string $uuid
     */
    public function __construct($uuid)
    {
        Assertion::nonEmptyString($uuid, 'uuid');
        Assertion::true(
            Uuid::isValid($uuid),
            sprintf('Given string "%s" is not a valid UUID', $uuid)
        );

        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param CollabPersonUuid $other
     * @return bool
     */
    public function equals(CollabPersonUuid $other)
    {
        return $this->uuid === $other->uuid;
    }

    public function __toString()
    {
        return sprintf('CollabPersonUuid(%s)', $this->uuid);
    }
}
