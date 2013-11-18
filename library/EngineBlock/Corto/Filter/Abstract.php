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

abstract class EngineBlock_Corto_Filter_Abstract
{
    protected $_server;

    public function __construct(EngineBlock_Corto_ProxyServer $server)
    {
        $this->_server = $server;
    }

    /**
     * @abstract
     * @return array
     */
    abstract protected function _getCommands();

    /**
     * Filter the response.
     *
     * @param array $response
     * @param array $responseAttributes
     * @param array $request
     * @param array $spEntityMetadata
     * @param array $idpEntityMetadata
     * @return void
     */
    public function filter(
        EngineBlock_Saml2_ResponseAnnotationDecorator &$response,
        array &$responseAttributes,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        array $spEntityMetadata,
        array $idpEntityMetadata
    )
    {
        /** @var SAML2_AuthnRequest $request */
        // Note that IDs are only unique per SP... we hope...
        $responseNameId = $response->getAssertion()->getNameId();

        $sessionKey = $spEntityMetadata['EntityId'] . '>' . $request->getId();
        if (isset($_SESSION[$sessionKey]['collabPersonId'])) {
            $collabPersonId = $_SESSION[$sessionKey]['collabPersonId'];
        }
        else if ($response->getCollabPersonId()) {
            $collabPersonId = $response->getCollabPersonId();
        }
        else if (isset($responseAttributes['urn:oid:1.3.6.1.4.1.1076.20.40.40.1'][0])) {
            $collabPersonId = $responseAttributes['urn:oid:1.3.6.1.4.1.1076.20.40.40.1'][0];
        }
        else if (!empty($responseNameId['Value'])) {
            $collabPersonId = $responseNameId['Value'];
        }
        else {
            $collabPersonId = null;
        }

        $commands = $this->_getCommands();

        /** @var EngineBlock_Corto_Filter_Command_Abstract $command */
        foreach ($commands as $command) {
            // Inject everything we have into the adapter
            $command->setProxyServer($this->_server);
            $command->setIdpMetadata($idpEntityMetadata);
            $command->setSpMetadata($spEntityMetadata);
            $command->setRequest($request);
            $command->setResponse($response);
            $command->setResponseAttributes($responseAttributes);
            $command->setCollabPersonId($collabPersonId);

            // Execute the command
            try {
                $command->execute();
            } catch (EngineBlock_Exception $e) {
                $e->idpEntityId = $idpEntityMetadata['EntityID'];
                $e->spEntityId  = $spEntityMetadata['EntityID'];
                $e->userId      = $collabPersonId;
                throw $e;
            }

            if (method_exists($command, 'getResponse')) {
                $response = $command->getResponse();
            }
            if (method_exists($command, 'getResponseAttributes')) {
                $responseAttributes = $command->getResponseAttributes();
            }
            if (method_exists($command, 'getCollabPersonId')) {
                $collabPersonId = $command->getCollabPersonId();
            }

            // Give the command a chance to stop filtering
            if (!$command->mustContinueFiltering()) {
                break;
            }
        }

        $_SESSION[$sessionKey]['collabPersonId'] = $collabPersonId;
    }
}
