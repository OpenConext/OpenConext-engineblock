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
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use Psr\Log\LoggerInterface;
use SAML2\Constants;
use SAML2\XML\saml\NameID;

class EngineBlock_Corto_Filter_Command_AddIdentityAttributes extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EngineBlock_Saml2_NameIdResolver
     */
    private $nameIdResolver;

    /**
     * @var EngineBlock_Arp_NameIdSubstituteResolver
     */
    private $substituteResolver;

    public function __construct(
        EngineBlock_Saml2_NameIdResolver $nameIdResolver,
        EngineBlock_Arp_NameIdSubstituteResolver $resolver,
        LoggerInterface $logger
    ) {
        $this->nameIdResolver = $nameIdResolver;
        $this->substituteResolver = $resolver;
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
        $this->logger->info('Executing the AddIdentityAttributes output filter');

        // Note that we try to service the final destination SP, if we know them and are allowed to do so.
        $destinationMetadata = EngineBlock_SamlHelper::getDestinationSpMetadata(
            $this->_serviceProvider,
            $this->_request,
            $this->_server->getRepository()
        );

        $isResolved = false;

        $arp = $destinationMetadata->getAttributeReleasePolicy();
        if (!is_null($arp)) {
            // Now check if we should update the NameID value according to the 'use_as_nameid' directive in the ARP.
            $arpSubstitute = $this->substituteResolver->findNameIdSubstitute($arp, $this->getResponseAttributes());
            if ($arpSubstitute !== null) {
                $nameId = new NameID();
                $nameId->setFormat(Constants::NAMEID_UNSPECIFIED);
                $nameId->setValue($arpSubstitute);
                $isResolved = true;
                $this->_response->getAssertion()->setNameId($nameId);
            }
        }

        if (!$isResolved || !isset($nameId)){
            // Resolve what NameID we should send the destination.
            $resolver = new EngineBlock_Saml2_NameIdResolver($this->logger);
            $nameId = $resolver->resolve(
                $this->_request,
                $this->_response,
                $destinationMetadata,
                $this->_collabPersonId
            );

            $this->logger->info('Setting the NameId on the Assertion');
            $this->_response->getAssertion()->setNameId($nameId);
        }

        // If there's an ARP, but it does not contain the EPTI, we're done now.
        if ($arp instanceof AttributeReleasePolicy && !$arp->hasAttribute(Constants::EPTI_URN_MACE)) {
            return;
        }

        // We arrive here if either:
        // 1) the ARP is NULL, this means no ARP = let everything through including EPTI; or
        // 2) the ARP is not null and does contain the EPTI attribute.
        // In both cases, set the EPTI attribute.
        $this->logger->info('Adding the EduPersonTargetedId on the Assertion');
        $this->_responseAttributes[Constants::EPTI_URN_MACE] = [$nameId];
    }

    private function resolveNameId(ServiceProvider $destinationMetadata): NameID
    {
        // Resolve what NameID we should send the destination.
        return $this->nameIdResolver->resolve(
            $this->_request,
            $this->_response,
            $destinationMetadata,
            $this->_collabPersonId
        );
    }
}
