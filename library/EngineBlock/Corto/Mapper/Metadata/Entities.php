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

class EngineBlock_Corto_Mapper_Metadata_Entities
{
    private $_entities;

    /**
     * @param AbstractRole[] $entities
     */
    public function __construct(array $entities)
    {
        $this->_entities = $entities;
    }

    public function map()
    {
        return $this->mapTo(array());
    }

    public function mapTo(array $rootElement)
    {
        $rootElement[EngineBlock_Corto_XmlToArray::TAG_NAME_PFX] = 'md:EntitiesDescriptor';

        if (empty($this->_entities)) {
            return $rootElement;
        }

        $rootElement['md:EntityDescriptor'] = array();
        foreach ($this->_entities as $entity) {
            $rootElement['md:EntityDescriptor'][] = $this->_mapEntity($entity);
        }
        return $rootElement;
    }

    protected function _mapEntity(AbstractRole $entity)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity($entity);
        return $mapper->map();
    }
}
