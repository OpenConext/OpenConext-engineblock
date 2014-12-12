<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProviderEntity;

class EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AttributeConsumingService_RequestedAttributes
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