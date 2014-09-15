<?php

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