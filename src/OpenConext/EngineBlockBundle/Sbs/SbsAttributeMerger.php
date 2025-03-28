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
use OpenConext\EngineBlockBundle\Exception\InvalidSbsResponseException;

class SbsAttributeMerger
{

    /**
     * @var array
     */
    private $allowedAttributeNames;

    public function __construct(array $allowedAttributeNames)
    {
        $this->allowedAttributeNames = $allowedAttributeNames;
    }

    public function mergeAttributes(array $samlAttributes, array $sbsAttributes): array
    {
        $validAttributes = $this->validSbsAttributes($sbsAttributes);

        foreach ($validAttributes as $key => $value) {
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
     * @SuppressWarnings(PHPMD.UnusedLocalVariable) $value is never used in the foreach
     */
    private function validSbsAttributes(array $sbsAttributes): array
    {
        $validAttributes = [];
        $invalidKeys = [];

        foreach ($sbsAttributes as $key => $value) {
            if (in_array($key, $this->allowedAttributeNames, true)) {
                $validAttributes[$key] = $sbsAttributes[$key];
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
