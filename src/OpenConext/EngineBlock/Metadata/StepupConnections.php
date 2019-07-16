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

use JsonSerializable;

/**
 * Value object for IdP stepup connections
 *
 * @package OpenConext\EngineBlock\Metadata
 */
class StepupConnections implements JsonSerializable
{

    /**
     * @var array
     */
    private $connections = [];

    /**
     * @param array $connections
     */
    public function __construct(array $connections = array())
    {
        foreach ($connections as $entityId => $loa) {
            if (!empty($entityId) && !empty($loa)) {
                $this->connections[$entityId] = $loa;
            }
        }
    }

    /**
     * @return bool
     */
    public function hasConnections()
    {
        return count($this->connections) > 0;
    }

    /**
     * @param $entityId
     * @return string|null
     */
    public function getLoa($entityId)
    {
        if (!array_key_exists($entityId, $this->connections)) {
            return null;
        }
        return $this->connections[$entityId];
    }

    public function jsonSerialize()
    {
        return $this->connections;
    }
}
