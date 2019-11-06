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

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;

/**
 * TODO: Not yet removed with the story: https://www.pivotaltracker.com/story/show/164925138
 * this should have been made obsolete once all Metadata related logic is moved out of Corto
 */
class EngineBlock_Corto_Mapper_Metadata_EdugainDocument
{
    private $_id;
    private $_validUntil;
    private $_entities;
    private $_entity;

    /**
     * @param string $id
     * @param $validUntil
     */
    public function __construct($id, $validUntil)
    {
        $this->_id = $id;
        $this->_validUntil = $validUntil;
    }

    public function map()
    {
        $rootElement = array();
        $rootElement[EngineBlock_Corto_XmlToArray::COMMENT_PFX] = $this->_getTermsOfUse();
        $rootElement[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:md'] = 'urn:oasis:names:tc:SAML:2.0:metadata';
        $rootElement[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:mdui'] = 'urn:oasis:names:tc:SAML:metadata:ui';

        $rootElement[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'validUntil'] = $this->_validUntil;

        if (isset($this->_entities)) {
            $rootElement[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID'] = $this->_id;
            $rootElement['ds:Signature'] = EngineBlock_Corto_XmlToArray::PLACEHOLDER_VALUE;
            $rootElement = $this->_mapEntities($rootElement);
        }
        else if (isset($this->_entity)) {
            $rootElement['_entityID'] = EngineBlock_Corto_XmlToArray::PLACEHOLDER_VALUE;
            $rootElement[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'ID'] = $this->_id;
            $rootElement['ds:Signature'] = EngineBlock_Corto_XmlToArray::PLACEHOLDER_VALUE;
            $rootElement = $this->_mapEntity($rootElement);
        }
        else {
            throw new EngineBlock_Exception("Nothing to map! Provide entities or an entity");
        }
        return $rootElement;
    }

    protected function _mapEntities(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entities($this->_entities);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapEntity(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    /**
     * @param AbstractRole[] $entities
     * @return $this
     */
    public function setEntities(array $entities)
    {
        $this->_entities = $entities;
        return $this;
    }

    public function setEntity(AbstractRole $entity)
    {
        $this->_entity = $entity;
        return $this;
    }

    protected function _getTermsOfUse()
    {
        $settings = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer();

        return $settings->getOpenConextTermsOfUseUrl();
    }
}
