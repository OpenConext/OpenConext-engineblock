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
 * Attribute / NameId / Response manipulation / mangling
 */
class EngineBlock_Corto_Filter_Command_RunAttributeManipulations extends EngineBlock_Corto_Filter_Command_Abstract
    implements EngineBlock_Corto_Filter_Command_ResponseModificationInterface,
    EngineBlock_Corto_Filter_Command_ResponseAttributesModificationInterface
{
    const TYPE_SP  = 'sp';
    const TYPE_REQUESTER_SP = 'requester-sp';
    const TYPE_IDP = 'idp';

    private $_type;

    function __construct($type)
    {
        if (!in_array($type, array(self::TYPE_SP, self::TYPE_IDP, self::TYPE_REQUESTER_SP))) {
            throw new EngineBlock_Exception(sprintf('Invalid type for Attribute Manipulation: "%s"', $type));
        }
        $this->_type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseAttributes()
    {
        return $this->_responseAttributes;
    }

    public function execute()
    {
        if (!$this->_response->getIntendedNameId()) {
            $this->_response->setIntendedNameId($this->_collabPersonId);
        }

        if ($this->_type === self::TYPE_IDP) {
            $entity          = $this->_identityProvider;
            $serviceProvider = $this->_serviceProvider;
        }
        else if ($this->_type === self::TYPE_SP) {
            $entity          = $this->_serviceProvider;
            $serviceProvider = $entity;
        }
        else if ($this->_type === self::TYPE_REQUESTER_SP) {
            $entity = EngineBlock_SamlHelper::findRequesterServiceProvider(
                $this->_serviceProvider,
                $this->_request,
                $this->_server->getRepository(),
                EngineBlock_ApplicationSingleton::getLog()
            );
            if (!$entity) {
                return;
            }
            $serviceProvider = $entity;
        }
        else {
            throw new EngineBlock_Exception(
                sprintf('Attribute Manipulator encountered an unexpected type: "%s"', $this->_type)
            );
        }

        // Try entity specific file based manipulation from Service Registry
        $manipulator = new EngineBlock_Attributes_Manipulator_ServiceRegistry($this->_type);
        $manipulator->manipulate(
            $entity,
            $this->_collabPersonId,
            $this->_responseAttributes,
            $this->_response,
            $this->_identityProvider,
            $serviceProvider
        );

        $this->_response->setIntendedNameId($this->_collabPersonId);
    }
}
