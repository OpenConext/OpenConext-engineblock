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

/**
 * Validate if the IDP sending this response is allowed to connect to the SP that made the request.
 **/
class EngineBlock_Corto_Filter_Command_ValidateAllowedConnection extends EngineBlock_Corto_Filter_Command_Abstract
{
    public function execute()
    {
        if (!$this->_serviceProvider->isAllowed($this->_identityProvider->entityId)) {
            throw new EngineBlock_Corto_Exception_InvalidConnection(
                sprintf(
                    'Disallowed response by SP configuration. Response from IdP "%s" to SP "%s"',
                    $this->_identityProvider->entityId,
                    $this->_serviceProvider->entityId
                )
            );
        }
    }
}
