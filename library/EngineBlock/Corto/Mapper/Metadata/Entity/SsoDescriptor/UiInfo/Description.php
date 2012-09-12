<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo_Description
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
        foreach($this->_entity['Description'] as $displayLanguageCode => $description) {
            if(empty($description)) {
                continue;
            }

            if (!isset($rootElement['md:Extensions'])) {
                $rootElement['md:Extensions'] = array();
            }
            if (!isset($rootElement['md:Extensions']['mdui:UIInfo'])) {
                $rootElement['md:Extensions']['mdui:UIInfo'] = array(0=>array());
            }
            $rootElement['md:Extensions']['mdui:UIInfo'][0]['mdui:Description'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $displayLanguageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $description
            );
        }
        return $rootElement;
    }
}