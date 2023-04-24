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

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo
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
        $rootElement = $this->_mapDisplayName($rootElement);
        $rootElement = $this->_mapDescription($rootElement);
        $rootElement = $this->_mapLogo($rootElement);
        $rootElement = $this->_mapKeywords($rootElement);
        $rootElement = $this->_mapPrivacyStatementUrl($rootElement);
        return $rootElement;
    }

    protected function _mapDisplayName(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo_DisplayName($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapDescription(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo_Description($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapLogo(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo_Logo($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapKeywords(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo_Keywords($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapPrivacyStatementUrl(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo_PrivacyStatementUrl($this->_entity);
        return $mapper->mapTo($rootElement);
    }
}
