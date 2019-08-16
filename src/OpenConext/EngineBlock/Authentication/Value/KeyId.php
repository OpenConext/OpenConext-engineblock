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

use OpenConext\EngineBlock\Assert\Assertion;

final class KeyId
{
    /**
     * @var string
     */
    private $keyId;

    /**
     * @param string $keyId
     */
    public function __construct($keyId)
    {
        Assertion::nonEmptyString($keyId, 'keyId');

        $this->keyId = $keyId;
    }

    /**
     * @return string
     */
    public function getKeyId()
    {
        return $this->keyId;
    }

    /**
     * @param KeyId $other
     * @return bool
     */
    public function equals(KeyId $other)
    {
        return $this->keyId === $other->keyId;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('KeyId(%s)', $this->keyId);
    }
}
