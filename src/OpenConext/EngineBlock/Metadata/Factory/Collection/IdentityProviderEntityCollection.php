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

namespace OpenConext\EngineBlock\Metadata\Factory\Collection;

use ArrayIterator;
use IteratorAggregate;
use OpenConext\EngineBlock\Metadata\Factory\IdentityProviderEntityInterface;

class IdentityProviderEntityCollection implements IteratorAggregate
{
    private $map;

    public function add(IdentityProviderEntityInterface $identityProvider) : void
    {
        $this->map[$identityProvider->getEntityId()] = $identityProvider;
    }

    public function has(string $entityId) : bool
    {
        return array_key_exists($entityId, $this->map);
    }

    public function get(string $entityId) : IdentityProviderEntityInterface
    {
        return $this->map[$entityId];
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->map);
    }
}
