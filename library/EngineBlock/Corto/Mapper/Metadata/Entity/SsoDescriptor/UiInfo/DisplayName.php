<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo_DisplayName
{
    private $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (!isset($this->_entity['DisplayName'])) {
            return $rootElement;
        }
        foreach($this->_entity['DisplayName'] as $displayLanguageCode => $displayName) {
            if(empty($displayName)) {
                continue;
            }

            if (!isset($rootElement['md:Extensions'])) {
                $rootElement['md:Extensions'] = array();
            }
            if (!isset($rootElement['md:Extensions']['mdui:UIInfo'])) {
                $rootElement['md:Extensions']['mdui:UIInfo'] = array(0=>array());
            }
            $rootElement['md:Extensions']['mdui:UIInfo'][0]['mdui:DisplayName'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $displayLanguageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $displayName
            );
        }
        return $rootElement;
    }
}