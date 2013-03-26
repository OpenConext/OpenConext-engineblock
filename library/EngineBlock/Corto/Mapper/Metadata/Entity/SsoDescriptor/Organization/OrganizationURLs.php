<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_Organization_OrganizationURLs
{
    private $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (!isset($this->_entity['Organization']['URL'])) {
            return $rootElement;
        }

        foreach($this->_entity['Organization']['URL'] as $descriptionLanguageCode => $descriptionName) {
            if (empty($descriptionName)) {
                continue;
            }

            if (!isset($rootElement['md:OrganizationURL'])) {
                $rootElement['md:OrganizationURL'] = array();
            }
            $rootElement['md:OrganizationURL'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $descriptionLanguageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $descriptionName
            );
        }
        return $rootElement;
    }
}