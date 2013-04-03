<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_Organization_OrganizationDisplayNames
{
    private $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (!isset($this->_entity['Organization']['DisplayName'])) {
            return $rootElement;
        }

        foreach($this->_entity['Organization']['DisplayName'] as $descriptionLanguageCode => $descriptionName) {
            if (empty($descriptionName)) {
                continue;
            }

            if (!isset($rootElement['md:OrganizationDisplayName'])) {
                $rootElement['md:OrganizationDisplayName'] = array();
            }
            $rootElement['md:OrganizationDisplayName'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $descriptionLanguageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $descriptionName
            );
        }
        return $rootElement;
    }
}