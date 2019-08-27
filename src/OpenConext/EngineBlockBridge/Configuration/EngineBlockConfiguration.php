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

namespace OpenConext\EngineBlockBridge\Configuration;

use OpenConext\EngineBlock\Assert\Assertion;

final class EngineBlockConfiguration
{
    /**
     * @var array
     */
    private $configuration = [];

    public function __construct(array $configuration)
    {
        foreach ($configuration as $key => $value) {
            if (is_array($value)) {
                $this->configuration[$key] = new self($value);
            } else {
                $this->configuration[$key] = $value;
            }
        }
    }

    /**
     * @param string $path
     * @param null|mixed $default
     * @return null|mixed|EngineBlockConfiguration
     */
    public function get($path, $default = null)
    {
        Assertion::nonEmptyString($path, 'path');

        $subPaths = explode('.', $path);
        $subPath = array_shift($subPaths);

        if (!array_key_exists($subPath, $this->configuration)) {
            if (is_array($default) && empty($default)) {
                return new EngineBlockConfiguration([]);
            }
            return $default;
        }

        $value = $this->configuration[$subPath];

        if ($value instanceof EngineBlockConfiguration && !empty($subPaths)) {
            return $value->get(join('.', $subPaths), $default);
        }

        if (is_string($value)) {
            return str_replace('%%', '%', $value);
        }

        return $value;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [];

        foreach ($this->configuration as $key => $value) {
            if ($value instanceof EngineBlockConfiguration) {
                $result[$key] = $value->toArray();
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
