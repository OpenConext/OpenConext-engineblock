<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AttributeConsumingService_RequestedAttributes
{
    private $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (!isset($this->_entity['RequestedAttributes'])) {
            return $rootElement;
        }
        $rootElement['md:RequestedAttribute'] = array();
        foreach ($this->_entity['RequestedAttributes'] as $requestedAttribute) {
            $element = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Name' => $requestedAttribute['Name'],
            );
            if (isset($requestedAttribute['NameFormat'])) {
                $element[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'NameFormat'] = 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri';
            }
            if (!empty($requestedAttribute['Required'])) {
                $element[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'isRequired'] = 'true';
            } else {
                $element[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'isRequired'] = 'false';
            }
            $rootElement['md:RequestedAttribute'][] = $element;
        }

        return $rootElement;
    }
}