<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AttributeConsumingService_ServiceNames
{
    private $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (!isset($this->_entity['Name'])) {
            return $rootElement;
        }

        foreach($this->_entity['Name'] as $descriptionLanguageCode => $descriptionName) {
            if (empty($descriptionName)) {
                continue;
            }

            if (!isset($rootElement['md:ServiceName'])) {
                $rootElement['md:ServiceName'] = array();
            }
            $rootElement['md:ServiceName'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $descriptionLanguageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $descriptionName
            );
        }
        return $rootElement;
    }
}