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

class EngineBlock_Corto_Mapper_Metadata_Entity_Organization
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
        if (!$this->_entity->organizationEn && !$this->_entity->organizationNl) {
            return $rootElement;
        }
        $rootElement['md:Organization'] = array();

        $rootElement['md:Organization'] = $this->_mapOrganizationNames($rootElement['md:Organization']);
        $rootElement['md:Organization'] = $this->_mapOrganizationDisplayNames($rootElement['md:Organization']);
        $rootElement['md:Organization'] = $this->_mapOrganizationURLs($rootElement['md:Organization']);
        return $rootElement;
    }

    /**
     * @param array $rootElement
     * @return array
     */
    protected function _mapOrganizationNames(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_Organization_OrganizationNames($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    /**
     * @param array $rootElement
     * @return array
     */
    protected function _mapOrganizationDisplayNames(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_Organization_OrganizationDisplayNames($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    /**
     * @param array $rootElement
     * @return array
     */
    protected function _mapOrganizationURLs(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_Organization_OrganizationURLs($this->_entity);
        return $mapper->mapTo($rootElement);
    }
}
