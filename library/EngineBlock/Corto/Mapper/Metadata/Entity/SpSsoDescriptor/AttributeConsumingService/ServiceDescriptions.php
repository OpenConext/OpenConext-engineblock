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

class EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AttributeConsumingService_ServiceDescriptions
{
    /**
     * @var AbstractRole
     */
    private $_entity;

    public function __construct(AbstractRole $entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        $descriptions = array();
        if ($this->_entity->getMdui()->hasDescription('nl')) {
            $descriptions['nl'] = $this->_entity->getMdui()->getDescription('nl');
        }
        if ($this->_entity->getMdui()->hasDescription('en')) {
            $descriptions['en'] = $this->_entity->getMdui()->getDescription('en');
        }
        if ($this->_entity->getMdui()->hasDescription('pt')) {
            $descriptions['pt'] = $this->_entity->getMdui()->getDescription('pt');
        }
        if (empty($descriptions)) {
            return $rootElement;
        }

        $rootElement['md:ServiceDescription'] = array();
        foreach($descriptions as $languageCode => $value) {
            if (empty($value)) {
                continue;
            }

            $rootElement['md:ServiceDescription'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $languageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $value
            );
        }
        return $rootElement;
    }
}
