<?php

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

            foreach ($attributeValues as $attributeValue) {
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
                    $newAttributes[$attributeName][] = $attribute;
                } else {
                    $newAttributes[$attributeName][] = $attributeValue;
                }
            }
        }
        return $newAttributes;
    }
}