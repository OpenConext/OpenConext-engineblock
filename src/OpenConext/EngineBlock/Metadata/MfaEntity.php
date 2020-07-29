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

use JsonSerializable;
use OpenConext\EngineBlock\Assert\Assertion;

class MfaEntity implements JsonSerializable
{
    /**
     * @var string $entityId
     */
    private $entityId;

    /**
     * @var string $level
     */
    private $level;

    public function __construct(string $entityId, string $level)
    {
        $this->entityId = $entityId;
        $this->level = $level;
    }

    public static function fromJson(array $array): MfaEntity
    {
        Assertion::keyExists($array, 'entityId', 'MFA entityId must be specified');
        Assertion::keyExists($array, 'level', 'MFA entity level must be specified');
        Assertion::string($array['entityId'], 'MFA entityId must be of type string');
        Assertion::string($array['level'], 'MFA level must be of type string');

        return new self($array['entityId'], $array['level']);
    }

    public function entityId(): string
    {
        return $this->entityId;
    }

    /**
     * @param $entityId
     * @return string|null
     */
    public function level(): string
    {
        return $this->level;
    }

    public function jsonSerialize(): array
    {
        return [
            'entityId' => $this->entityId,
            'level' => $this->level,
        ];
    }
}
