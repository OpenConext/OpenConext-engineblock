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

use Countable;
use JsonSerializable;
use OpenConext\EngineBlock\Assert\Assertion;

class MfaEntityCollection implements JsonSerializable, Countable
{
    /**
     * @var array $entities
     */
    private $entities = [];

    /**
     * This method is used to build the collection from a metadata push
     * @param array $data
     * @return MfaEntityCollection
     * @throws \Assert\AssertionFailedException
     */
    public static function fromMetadataPush(array $data): MfaEntityCollection
    {
        $entities = [];
        foreach ($data as $mfaEntityData) {
            $entityId = (string) $mfaEntityData['name'];
            $level = (string) $mfaEntityData['level'];
            Assertion::keyNotExists($entities, $entityId, 'Duplicate SP entity ids are not allowed');
            $entities[$entityId] = new MfaEntity($entityId, $level);
        }
        return new self($entities);
    }

    /**
     * This method is used tto deserialize coin data
     * @param array $data
     * @return MfaEntityCollection
     * @throws \Assert\AssertionFailedException
     */
    public static function fromCoin(array $data): MfaEntityCollection
    {
        $entities = [];
        foreach ($data as $mfaEntityData) {
            $entity = MfaEntity::fromJson($mfaEntityData);
            Assertion::keyNotExists($entities, $entity->entityId(), 'Duplicate SP entity ids are not allowed');
            $entities[$entity->entityId()] = $entity;
        }
        return new self($entities);
    }

    public function findByEntityId(string $entityId): ?MfaEntity
    {
        if (!array_key_exists($entityId, $this->entities)) {
            return null;
        }
        return $this->entities[$entityId];
    }

    public function count(): int
    {
        return count($this->entities);
    }

    /**
     * @param MfaEntity[] $entities
     */
    private function __construct(array $entities)
    {
        Assertion::allIsInstanceOf($entities, MfaEntity::class);
        $this->entities = $entities;
    }

    public function jsonSerialize(): array
    {
        return $this->entities;
    }
}
