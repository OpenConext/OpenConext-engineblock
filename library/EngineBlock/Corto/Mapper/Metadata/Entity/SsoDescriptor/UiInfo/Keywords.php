<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo_Keywords
{
    private $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (!isset($this->_entity['Keywords'])) {
            return $rootElement;
        }
        
        if (!isset($rootElement['md:Extensions'])) {
            $rootElement['md:Extensions'] = array();
        }
        if (!isset($rootElement['md:Extensions']['mdui:UIInfo'])) {
            $rootElement['md:Extensions']['mdui:UIInfo'] = array(0=>array());
        }
        $uiInfo = &$rootElement['md:Extensions']['mdui:UIInfo'][0];
        foreach ($this->_entity['Keywords'] as $lang => $name) {
            if (trim($name)==='') {
                continue;
            }
            if (!isset($uiInfo['mdui:Keywords'])) {
                $uiInfo['mdui:Keywords'] = array();
            }
            $uiInfo['mdui:Keywords'][] = array(
                array(
                    '_xml:lang' => $lang,
                    '__v' => $name,
                ),
            );
        }
        return $rootElement;
    }
}