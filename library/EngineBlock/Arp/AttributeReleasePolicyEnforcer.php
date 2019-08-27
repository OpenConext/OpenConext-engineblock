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

use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;

class EngineBlock_Arp_AttributeReleasePolicyEnforcer
{
    public function enforceArp(AttributeReleasePolicy $arp = null, $responseAttributes, $showSources = false)
    {
        if (!$arp) {
            if ($showSources) {
                $newAttributes = array();
                foreach ($responseAttributes as $attributeName => $attributeValues) {
                    foreach ($attributeValues as $attributeValue) {
                        if (!isset($newAttributes[$attributeName])) {
                            $newAttributes[$attributeName] = array();
                        }
                        $attribute = array(
                            'value' => $attributeValue,
                            'source' => 'idp',
                        );
                        $newAttributes[$attributeName][] = $attribute;
                    }
                }
                $responseAttributes = $newAttributes;
            }

            return $responseAttributes;
        }

        $newAttributes = array();
        foreach ($responseAttributes as $attributeName => $attributeValues) {
            if (!$arp->hasAttribute($attributeName)) {
                continue;
            }

            // The attributeIndex is kept to preserve the original keys in $newAttributes
            // this to be able to remove the matching attribute value types
            foreach ($attributeValues as $attributeIndex => $attributeValue) {
                if (!$arp->isAllowed($attributeName, $attributeValue)) {
                    EngineBlock_ApplicationSingleton::getLog()->info(
                        "ARP: non allowed attribute value '$attributeValue' for attribute '$attributeName'"
                    );
                    continue;
                }

                if (!isset($newAttributes[$attributeName])) {
                    $newAttributes[$attributeName] = array();
                }

                if ($showSources) {
                    $attribute = array(
                        'value' => $attributeValue,
                        'source' => $arp->getSource($attributeName),
                    );
                    $newAttributes[$attributeName][$attributeIndex] = $attribute;
                } else {
                    $newAttributes[$attributeName][$attributeIndex] = $attributeValue;
                }
            }
        }
        return $newAttributes;
    }

    /**
     * Update the attribute value types
     *
     * The value types array should match the index of the attributes array.
     * This method should be used after enforcing the attribute release
     * policy.
     *
     * @param array $attributes
     * @param array $attributeValueTypes
     * @return array
     */
    public function updateAttributeValueTypes(array $attributes, array $attributeValueTypes)
    {
        $newAttributeValueTypes = [];
        foreach ($attributes as $attributeIdentifier => $attributeValues) {
            if (isset($attributeValueTypes[$attributeIdentifier])) {
                foreach ($attributeValues as $attributeIndex => $attributeValue) {
                    $newAttributeValueTypes[$attributeIndex] = array_intersect_key($attributeValue, $attributes[$attributeIndex]);
                }
            }
        }
        return $newAttributeValueTypes;
    }
}
