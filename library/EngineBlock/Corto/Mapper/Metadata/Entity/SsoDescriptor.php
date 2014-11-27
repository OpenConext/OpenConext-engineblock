<?php

abstract class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor
{
    protected $_entity;

    protected function _mapCertificates(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_Certificates($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    /**
     * @param array $rootElement
     * @return array
     */
    protected function _mapSingleLogoutService(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_SingleLogoutService($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapNameIdFormats(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_NameIdFormat($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapUiInfo(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo($this->_entity);
        return $mapper->mapTo($rootElement);
    }
}