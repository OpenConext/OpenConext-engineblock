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

/**
 * An indexed service is a Service definition with an explicit ordering in the form of an index.
 * @package OpenConext\EngineBlock\Metadata
 */
class IndexedService extends Service
{
    /**
     * @var int
     */
    public $serviceIndex;

    /**
     * Note that null and false are NOT the same in this context.
     *
     * @var bool|null
     */
    public $isDefault = null;

    /**
     * @param string $location
     * @param string $binding
     * @param $serviceIndex
     * @param bool|null $isDefault
     */
    public function __construct($location, $binding, $serviceIndex, $isDefault = null)
    {
        $this->isDefault    = $isDefault;
        $this->serviceIndex = $serviceIndex;

        parent::__construct($location, $binding);
    }

    /**
     * A convenience static constructor for the IndexedService.
     * @param array $indexedService
     * @return IndexedService
     */
    public static function indexedServiceFromArray(array $indexedService): IndexedService
    {
        return new self($indexedService["location"],
            $indexedService["binding"],
            $indexedService["serviceIndex"],
            $indexedService["isDefault"]
        );
    }
}
