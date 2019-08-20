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

class EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AttributeConsumingService_RequestedAttributes
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
        if (!isset($this->_entity->requestedAttributes)) {
            return $rootElement;
        }
        $rootElement['md:RequestedAttribute'] = array();
        foreach ($this->_entity->requestedAttributes as $requestedAttribute) {
            $element = array();

            $ATTRIBUTE_PREFIX = EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX;
            $element[$ATTRIBUTE_PREFIX . 'Name']       = $requestedAttribute->name;
            $element[$ATTRIBUTE_PREFIX . 'NameFormat'] = $requestedAttribute->nameFormat;
            $element[$ATTRIBUTE_PREFIX . 'isRequired'] = $requestedAttribute->required ? 'true' : 'false';

            $rootElement['md:RequestedAttribute'][] = $element;
        }
        return $rootElement;
    }
}
