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

class EngineBlock_Corto_Mapper_Metadata_Entity
{
    private $_entity;

    /**
     * @param AbstractRole $entity
     */
    public function __construct(AbstractRole $entity)
    {
        $this->_entity = $entity;
    }

    public function map()
    {
        return $this->mapTo(array());
    }

    public function mapTo(array $rootElement)
    {
        if (empty($this->_entity)) {
            $rootElement[EngineBlock_Corto_XmlToArray::TAG_NAME_PFX] = 'md:EntityDescriptor';
            return $rootElement;
        }

        $rootElement[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'entityID'] = $this->_entity->entityId;
        $rootElement[EngineBlock_Corto_XmlToArray::TAG_NAME_PFX] = 'md:EntityDescriptor';

        $rootElement = $this->_mapIdpSsoDescriptor($rootElement);
        $rootElement = $this->_mapSpSsoDescriptor($rootElement);
        $rootElement = $this->_mapOrganization($rootElement);
        $rootElement = $this->_mapContactPersons($rootElement);

        return $rootElement;
    }

    protected function _mapIdpSsoDescriptor(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_IdpSsoDescriptor($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapSpSsoDescriptor(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapContactPersons(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_ContactPersons($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapOrganization(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_Organization($this->_entity);
        return $mapper->mapTo($rootElement);
    }
}
