<?php

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
}
