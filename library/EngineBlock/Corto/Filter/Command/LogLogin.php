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

use OpenConext\EngineBlockBridge\Logger\AuthenticationLoggerAdapter;

class EngineBlock_Corto_Filter_Command_LogLogin extends EngineBlock_Corto_Filter_Command_Abstract
{
    /**
     * @var AuthenticationLoggerAdapter
     */
    private $authenticationLogger;

    public function __construct(AuthenticationLoggerAdapter $authenticationLogger)
    {
        $this->authenticationLogger = $authenticationLogger;
    }

    public function execute()
    {
        if (!$this->_collabPersonId) {
            throw new EngineBlock_Corto_Filter_Command_Exception_PreconditionFailed(
                'Missing collabPersonId'
            );
        }

        // Get the Requester chain, which starts at the oldest (farthest away from us SP) and ends with our next hop.
        $requesterChain = EngineBlock_SamlHelper::getSpRequesterChain(
            $this->_serviceProvider,
            $this->_request,
            $this->_server->getRepository()
        );

        // Remove the SP that is our next hop
        array_pop($requesterChain);

        $this->authenticationLogger->logLogin(
            $this->_serviceProvider,
            $this->_identityProvider,
            $this->_collabPersonId,
            $this->_request->getKeyId(),
            $requesterChain
        );
    }
}
