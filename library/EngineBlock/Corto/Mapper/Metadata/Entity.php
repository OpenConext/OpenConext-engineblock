<?php

class EngineBlock_Corto_Mapper_Metadata_Entity
{
    private $_entity;

    public function __construct($entity)
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

        $rootElement[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'entityID'] = $this->_entity['EntityID'];
        $rootElement[EngineBlock_Corto_XmlToArray::TAG_NAME_PFX] = 'md:EntityDescriptor';

        $rootElement = $this->_mapIdpSsoDescriptor($rootElement);
        $rootElement = $this->_mapSpSsoDescriptor($rootElement);
        $rootElement = $this->_mapOrganization($rootElement);
        $rootElement = $this->_mapContactPersons($rootElement);

        return $rootElement;
    }

    protected function _mapIdpSsoDescriptor($rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_IdpSsoDescriptor($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapSpSsoDescriptor($rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapContactPersons($rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_ContactPersons($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    /**
     * @param array $rootElement
     * @return array
     */
    protected function _mapOrganization(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_Organization($this->_entity);
        return $mapper->mapTo($rootElement);
    }
}