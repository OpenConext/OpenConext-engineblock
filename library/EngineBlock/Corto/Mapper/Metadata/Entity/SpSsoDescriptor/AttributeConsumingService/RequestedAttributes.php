<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AttributeConsumingService_RequestedAttributes
{
    private $_entity;

    const URN_OASIS_NAMES_TC_SAML_2_0_ATTRNAME_FORMAT_URI =
        EngineBlock_Corto_Module_Service_Metadata_ArpRequestedAttributes::URN_OASIS_NAMES_TC_SAML_2_0_ATTRNAME_FORMAT_URI;

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
            $element = array();

            $element[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Name'] = $requestedAttribute['Name'];

            $nameFormat = isset($requestedAttribute['NameFormat']) ? $requestedAttribute['NameFormat'] : self::URN_OASIS_NAMES_TC_SAML_2_0_ATTRNAME_FORMAT_URI;
            $element[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'NameFormat'] = $nameFormat;

            $element[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'isRequired'] = empty($requestedAttribute['Required']) ? 'false' : 'true';

            $rootElement['md:RequestedAttribute'][] = $element;
        }
        return $rootElement;
    }
}