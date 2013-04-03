<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_Organization_OrganizationNames
{
    private $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (!isset($this->_entity['Organization']['Name'])) {
            return $rootElement;
        }

        foreach($this->_entity['Organization']['Name'] as $descriptionLanguageCode => $descriptionName) {
            if (empty($descriptionName)) {
                continue;
            }

            if (!isset($rootElement['md:OrganizationName'])) {
                $rootElement['md:OrganizationName'] = array();
            }
            $rootElement['md:OrganizationName'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $descriptionLanguageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $descriptionName
            );
        }
        return $rootElement;
    }
}