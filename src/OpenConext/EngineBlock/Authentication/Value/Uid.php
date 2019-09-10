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

/**
 * Value Object representing an the value of an attribute with identifier urn:mace:dir:attribute-def:uid
 * (or urn:oid:0.9.2342.19200300.100.1.1)
 */
final class Uid
{
    const URN_MACE = 'urn:mace:dir:attribute-def:uid';

    /**
     * @var string
     */
    private $uid;

    /**
     * @param string $uid
     */
    public function __construct($uid)
    {
        Assertion::nonEmptyString($uid, 'uid');

        $this->uid = $uid;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param Uid $other
     * @return bool
     */
    public function equals(Uid $other)
    {
        return $this->uid === $other->uid;
    }

    public function __toString()
    {
        return sprintf('Uid(%s)', $this->uid);
    }
}
