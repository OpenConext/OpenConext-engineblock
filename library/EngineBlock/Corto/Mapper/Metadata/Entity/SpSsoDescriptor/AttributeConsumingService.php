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

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;

class EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AttributeConsumingService
{
    /**
     * @var ServiceProvider
     */
    private $_entity;

    public function __construct(ServiceProvider $entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (empty($this->_entity->requestedAttributes) || empty($this->_entity->nameEn) || empty($this->_entity->nameEn)) {
            return $rootElement;
        }
        $rootElement['md:AttributeConsumingService'] = array(
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'index' => 0,
        );

        $rootElement['md:AttributeConsumingService'] = $this->_mapServiceNames($rootElement['md:AttributeConsumingService']);
        $rootElement['md:AttributeConsumingService'] = $this->_mapServiceDescriptions($rootElement['md:AttributeConsumingService']);
        $rootElement['md:AttributeConsumingService'] = $this->_mapRequestedAttributes($rootElement['md:AttributeConsumingService']);
        return $rootElement;
    }

    protected function _mapServiceNames(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AttributeConsumingService_ServiceNames($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapServiceDescriptions(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AttributeConsumingService_ServiceDescriptions($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapRequestedAttributes(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AttributeConsumingService_RequestedAttributes($this->_entity);
        return $mapper->mapTo($rootElement);
    }
}
