<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AttributeConsumingService_ServiceDescriptions
{
    private $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (!isset($this->_entity['Description'])) {
            return $rootElement;
        }

        foreach($this->_entity['Description'] as $descriptionLanguageCode => $descriptionDescription) {
            if (empty($descriptionDescription)) {
                continue;
            }

            if (!isset($rootElement['md:ServiceDescription'])) {
                $rootElement['md:ServiceDescription'] = array();
            }
            $rootElement['md:ServiceDescription'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $descriptionLanguageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $descriptionDescription
            );
        }
        return $rootElement;
    }
}