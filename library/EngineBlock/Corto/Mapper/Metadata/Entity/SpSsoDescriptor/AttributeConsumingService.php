<?php

use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProviderEntity;

class EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AttributeConsumingService
{
    /**
     * @var ServiceProviderEntity
     */
    private $_entity;

    public function __construct(ServiceProviderEntity $entity)
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