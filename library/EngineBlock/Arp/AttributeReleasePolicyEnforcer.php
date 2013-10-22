<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class EngineBlock_Arp_AttributeReleasePolicyEnforcer
{
    public function enforceArp($arp, $responseAttributes)
    {
        if (!$arp) {
            return $responseAttributes;
        }
        $newAttributes = array();
        if (empty($arp['attributes'])) {
            EngineBlock_ApplicationSingleton::getLog()->info(
                "empty non-null ARP: no attributes allowed"
            );
            return $newAttributes;
        }
        foreach ($responseAttributes as $attribute => $attributeValues) {
            if (!isset($arp['attributes'][$attribute])) {
                EngineBlock_ApplicationSingleton::getLog()->info(
                    "ARP: non allowed attribute $attribute"
                );
                continue;
            }
            $allowedValues = $arp['attributes'][$attribute];
            if (in_array('*', $allowedValues)) {
                // Pass through all values
                $newAttributes[$attribute] = $attributeValues;
                continue;
            }
            foreach ($attributeValues as $attributeValue) {
                if (in_array($attributeValue, $allowedValues)) {
                    $this->_addAttributeValue($newAttributes, $attribute, $attributeValue);
                } else {
                    //Prefix matching check
                    foreach ($allowedValues as $allowedValue) {
                        $suffix = substr($allowedValue, 0, -1);
                        if ($this->_endsWith($allowedValue, '*') &&
                            $this->_startsWith($attributeValue, $suffix)
                        ) {
                            $this->_addAttributeValue($newAttributes, $attribute, $attributeValue);
                        }
                    }
                }
            }
        }
        return $newAttributes;
    }

    protected function _addAttributeValue(&$newAttributes, $attributeName, $attributeValue)
    {
        if (!isset($newAttributes[$attributeName])) {
            $newAttributes[$attributeName] = array();
        }
        $newAttributes[$attributeName][] = $attributeValue;
    }

    protected function _endsWith($str, $suffix)
    {
        return substr($str, -strlen($suffix)) === $suffix;
    }

    protected function _startsWith($str, $suffix)
    {
        return strpos($str, $suffix) === 0;
    }
}