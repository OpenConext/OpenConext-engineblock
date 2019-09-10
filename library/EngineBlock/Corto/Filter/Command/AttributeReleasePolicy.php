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

class EngineBlock_Corto_Filter_Command_AttributeReleasePolicy extends EngineBlock_Corto_Filter_Command_Abstract implements
    EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface,
    EngineBlock_Corto_Filter_Command_ResponseAttributeValueTypesModificationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function getResponseAttributeValueTypes()
    {
        return $this->_responseAttributeValueTypes;
    }

    public function execute()
    {
        $logger = EngineBlock_ApplicationSingleton::getLog();
        $enforcer = new EngineBlock_Arp_AttributeReleasePolicyEnforcer();
        $attributes = $this->_responseAttributes;

        // Get the Requester chain, which starts at the oldest (farthest away from us SP) and ends with our next hop.
        $requesterChain = EngineBlock_SamlHelper::getSpRequesterChain(
            $this->_serviceProvider,
            $this->_request,
            $this->_server->getRepository()
        );
        // Note that though we should traverse in reverse ordering, it doesn't make a difference.
        // A then B filter or B then A filter are equivalent.
        foreach ($requesterChain as $spMetadata) {
            $spEntityId = $spMetadata->entityId;

            $arp = $spMetadata->getAttributeReleasePolicy();

            if (!$arp) {
                continue;
            }

            $logger->info("Applying attribute release policy for $spEntityId");
            $attributes = $enforcer->enforceArp($arp, $attributes);

            $this->_responseAttributeValueTypes = $enforcer->updateAttributeValueTypes(
                $attributes,
                $this->_responseAttributeValueTypes
            );
        }

        $this->_responseAttributes = $attributes;
    }

    protected function getMetadataRepository()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getMetadataRepository();
    }
}
