<?php

use OpenConext\Component\EngineBlockMetadata\AttributeReleasePolicy;

class EngineBlock_Arp_AttributeReleasePolicyEnforcer
{
    public function enforceArp(AttributeReleasePolicy $arp = null, $responseAttributes)
    {
        if (!$arp) {
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

                $newAttributes[$attributeName][] = $attributeValue;
            }
        }
        return $newAttributes;
    }
}