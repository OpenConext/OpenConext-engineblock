<?php

/**
 * Copyright 2019 SURFnet B.V.
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

use OpenConext\EngineBlock\Exception\RuntimeException;
use OpenConext\EngineBlock\Message\RequestId;

class ConsentMap
{
    /**
     * @var ConsentInterface[]
     */
    private $consent;

    /**
     * @param RequestId $requestId
     * @param ConsentInterface $consent
     */
    public function add(RequestId $requestId, ConsentInterface $consent)
    {
        if ($this->has($requestId)) {
            throw new RuntimeException(
                sprintf('The authentication identified with ID "%s", has already given consent', $requestId)
            );
        }
        $this->consent[(string) $requestId] = $consent;
    }

    public function has(RequestId $requestId)
    {
        return isset($this->consent[(string) $requestId]);
    }

    /**
     * @param RequestId $requestId
     * @return ConsentInterface|null
     */
    public function find(RequestId $requestId)
    {
        if ($this->has($requestId)) {
            return $this->consent[(string) $requestId];
        }
        return null;
    }
}
