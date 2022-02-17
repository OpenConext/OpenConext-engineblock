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

use Psr\Log\LoggerInterface;
use SAML2\Constants;

class EngineBlock_Corto_Filter_Command_AddEduPersonTargetedId extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    /**
     * Resolve the eduPersonTargetedId we should send.
     */
    public function execute()
    {
        $this->logger->info('Executing the AddEduPersonTargetedId output filter');

        // Note that we try to service the final destination SP, if we know them and are allowed to do so.
        $destinationMetadata = EngineBlock_SamlHelper::getDestinationSpMetadata(
            $this->_serviceProvider,
            $this->_request,
            $this->_server->getRepository()
        );

        // Find out if the EduPersonTargetedId is in the ARP of the destination SP.
        // If the ARP is NULL this means no ARP = let everything through including ePTI.
        // Otherwise only add ePTI if it's acutally in the ARP.
        $arp = $destinationMetadata->getAttributeReleasePolicy();
        if (!is_null($arp) && !$arp->hasAttribute(Constants::EPTI_URN_MACE)) {
            return;
        }

        // Resolve what NameID we should send the destination.
        $resolver = new EngineBlock_Saml2_NameIdResolver($this->logger);
        $nameId = $resolver->resolve(
            $this->_request,
            $this->_response,
            $destinationMetadata,
            $this->_collabPersonId
        );

        $this->_responseAttributes[Constants::EPTI_URN_MACE] = [ $nameId ];
    }
}
