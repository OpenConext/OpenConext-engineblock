<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class EngineBlock_Corto_Module_Bindings extends Corto_Module_Bindings
{
    /**
     * @var EngineBlock_Corto_CoreProxy
     */
    protected $_server;

    protected function _receiveMessage($key)
    {
        $message = parent::_receiveMessage($key);

        if ($key == Corto_Module_Bindings::KEY_REQUEST) {
            // We're dealing with a request, on its way towards the idp. If there's a VO context, we need to store it in the request.

            $voContext = $this->_server->getVirtualOrganisationContext();
            if ($voContext != NULL) {
                $message['__'][EngineBlock_Corto_CoreProxy::VO_CONTEXT_PFX] = $voContext;
            }

        }

        return $message;
    }

    /**
     * Verify if a message has an issuer that is known to us. If not, it
     * throws a Corto_Module_Bindings_VerificationException.
     * @param array $message
     * @throws Corto_Module_Bindings_VerificationException
     */
    protected function _verifyKnownIssuer(array $message)
    {
        $messageIssuer = $message['saml:Issuer']['__v'];
        $destination = $message['_Destination'];
        try {
            $remoteEntity = $this->_server->getRemoteEntity($messageIssuer);
        } catch (Corto_ProxyServer_Exception $e) {
            throw new EngineBlock_Corto_Exception_UnknownIssuerException(
                "Issuer '{$messageIssuer}' is not a known remote entity? (please add SP/IdP to Remote Entities)",
                $messageIssuer,
                $destination
            );
        }
        return $remoteEntity;
    }
}