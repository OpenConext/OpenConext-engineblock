<?php

/**
 * Copyright 2014 SURFnet B.V.
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

use OpenConext\EngineBlock\Metadata\RequestedAttribute;
use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;

/**
 * Add the RequestedAttributes for the AttributeConsumingService section in the SPSSODescriptor based on the ARP of the SP
 */

class EngineBlock_Corto_Module_Service_Metadata_ArpRequestedAttributes
{
    public function addRequestAttributes(AbstractRole $entity)
    {
        if (!$entity instanceof ServiceProvider) {
            return $entity;
        }

        $arp = $entity->getAttributeReleasePolicy();
        if (!$arp) {
            return $entity;
        }

        $attributeNames = $arp->getAttributeNames();

        $entity->requestedAttributes = array();
        foreach ($attributeNames as $attributeName) {
            $entity->requestedAttributes[] = new RequestedAttribute($attributeName);
        }

        return $entity;
    }


    protected function getMetadataRepository()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getMetadataRepository();
    }
}
