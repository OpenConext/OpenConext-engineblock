<?php

/**
 * Copyright 2025 SURFnet B.V.
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

namespace OpenConext\EngineBlockBundle\Sbs;

use EngineBlock_ApplicationSingleton;

class SbsAttributeMerger
{

    /**
     * @var array Map of attribute names to their value types (e.g., 'name' => 'xs:string')
     */
    private $allowedAttributeTypes;

    /**
     * @var array Tracks the value types of merged attributes
     */
    private $mergedAttributeTypes = [];

    public function __construct(array $allowedAttributeTypes)
    {
        foreach ($allowedAttributeTypes as $key => $value) {
            assert(is_string($key), 'All keys in allowedAttributeTypes must be strings');
            assert(is_string($value), 'All values in allowedAttributeTypes must be strings');
        }
        $this->allowedAttributeTypes = $allowedAttributeTypes;
    }

    public function mergeAttributes(array $samlAttributes, array $sbsAttributes): array
    {
        // Reset merged attribute types for this merge operation
        $this->mergedAttributeTypes = [];

        $validAttributes = $this->validSbsAttributes($sbsAttributes);

        foreach ($validAttributes as $key => $value) {
            // Track the value type for this merged attribute
            if (isset($this->allowedAttributeTypes[$key])) {
                $this->mergedAttributeTypes[$key] = $this->allowedAttributeTypes[$key];
            }

            if (!isset($samlAttributes[$key])) {
                $samlAttributes[$key] = $value;
                continue;
            }

            if (is_array($value) && is_array($samlAttributes[$key])) {
                // Merge and remove duplicates if both values are arrays
                $samlAttributes[$key] = array_unique(array_merge($samlAttributes[$key], $value));
                continue;
            }

            $samlAttributes[$key] = $value;
        }

        return $samlAttributes;
    }

    /**
     * Get the value types for attributes that were merged in the last mergeAttributes() call
     *
     * @return array Map of attribute names to their value types
     */
    public function getMergedAttributeTypes(): array
    {
        return $this->mergedAttributeTypes;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable) $value is never used in the foreach
     */
    private function validSbsAttributes(array $sbsAttributes): array
    {
        $validAttributes = [];
        $invalidKeys = [];

        foreach ($sbsAttributes as $key => $value) {
            if (array_key_exists($key, $this->allowedAttributeTypes)) {
                $validAttributes[$key] = $value;
            } else {
                $invalidKeys[] = $key;
            }
        }

        if (!empty($invalidKeys)) {
            $application = EngineBlock_ApplicationSingleton::getInstance();
            $log = $application->getLogInstance();
            $log->warning(sprintf('Attributes "%s" is not allowed to be overwritten by SBS.', implode(', ', $invalidKeys)));
        }

        return $validAttributes;
    }
}
