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

/**
 * SetNameId command, sets the proper NameID for the Response.
 *
 * Note that because SAML2 Assertion Subject NameID elements are intended for the next hop only,
 * we don't take SP proxies into account. Whatever OUR SP wants as a NameID is what it gets.
 * If THEIR SP is known to us and wants a different NameID they'll just have to use the eduPersonTargetedId.
 */
class EngineBlock_Corto_Filter_Command_SetNameId extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseModificationInterface
{
    /** @var LoggerInterface */
    private $logger;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Resolve what NameID we should send to our SP and set it in the Assertion.
     */
    public function execute()
    {
        $this->logger->info('Executing the SetNameId output filter');
        $resolver = new EngineBlock_Saml2_NameIdResolver($this->logger);

        // Note that we try to service the final destination SP, if we know them and are allowed to do so.
        $destinationMetadata = EngineBlock_SamlHelper::getDestinationSpMetadata(
            $this->_serviceProvider,
            $this->_request,
            $this->_server->getRepository()
        );

        $nameId = $resolver->resolve(
            $this->_request,
            $this->_response,
            $destinationMetadata,
            $this->_collabPersonId
        );

        $this->_response->getAssertion()->setNameId($nameId);
    }
}
