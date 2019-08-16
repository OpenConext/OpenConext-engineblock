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

use EngineBlock_Corto_Filter_Command_CollabPersonIdModificationInterface as CollabPersonIdModificationInterface;
use EngineBlock_Corto_Filter_Command_ResponseAttributeSourcesModificationInterface as ResponseAttributeSourcesModificationInterface;
use EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface as ResponseAttributesModificationInterface;
use EngineBlock_Corto_Filter_Command_ResponseAttributeValueTypesModificationInterface as ResponseAttributeValueTypesModificationInterface;
use EngineBlock_Corto_Filter_Command_ResponseModificationInterface as ResponseModificationInterface;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use SAML2\AuthnRequest;

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
    abstract public function getCommands();

    /**
     * Filter the response.
     *
     * @param EngineBlock_Saml2_ResponseAnnotationDecorator     $response
     * @param array                                             $responseAttributes
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     * @param ServiceProvider                             $serviceProvider
     * @param IdentityProvider                            $identityProvider
     * @throws EngineBlock_Exception
     * @throws Exception
     *
     * @see \EngineBlock_Corto_ProxyServer::callAttributeFilter is where this filter is applied.
     */
    public function filter(
        EngineBlock_Saml2_ResponseAnnotationDecorator &$response,
        array &$responseAttributes,
        EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request,
        ServiceProvider $serviceProvider,
        IdentityProvider $identityProvider
    )
    {
        /** @var AuthnRequest $request */
        // Note that IDs are only unique per SP... we hope...
        $responseNameId = $response->getAssertion()->getNameId();

        $sessionKey = $serviceProvider->entityId . '>' . $request->getId();
        if (isset($_SESSION[$sessionKey]['collabPersonId'])) {
            $collabPersonId = $_SESSION[$sessionKey]['collabPersonId'];
        }
        else if ($response->getCollabPersonId()) {
            $collabPersonId = $response->getCollabPersonId();
        }
        else if ($responseNameId->value) {
            $collabPersonId = $responseNameId->value;
        }
        else {
            $collabPersonId = null;
        }

        $commands = $this->getCommands();

        /** @var EngineBlock_Corto_Filter_Command_Abstract $command */
        foreach ($commands as $command) {
            // Inject everything we have into the adapter
            $command->setProxyServer($this->_server);
            $command->setIdentityProvider($identityProvider);
            $command->setServiceProvider($serviceProvider);
            $command->setRequest($request);
            $command->setResponse($response);
            $command->setResponseAttributes($responseAttributes);
            $command->setCollabPersonId($collabPersonId);

            // Execute the command
            try {
                $command->execute();
            } catch (EngineBlock_Exception $e) {
                $e->idpEntityId = $identityProvider->entityId;
                $e->spEntityId  = $serviceProvider->entityId;
                $e->userId      = $collabPersonId;
                throw $e;
            }

            if ($command instanceof ResponseModificationInterface) {
                $response = $command->getResponse();
            }
            if ($command instanceof ResponseAttributesModificationInterface) {
                $responseAttributes = $command->getResponseAttributes();
            }
            if ($command instanceof ResponseAttributeValueTypesModificationInterface) {
                $response->getAssertion()->setAttributesValueTypes($command->getResponseAttributeValueTypes());
            }
            if ($command instanceof ResponseAttributeSourcesModificationInterface) {
                $_SESSION[$request->getId()]['attribute_sources'] = $command->getResponseAttributeSources();
            }
            if ($command instanceof CollabPersonIdModificationInterface) {
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
