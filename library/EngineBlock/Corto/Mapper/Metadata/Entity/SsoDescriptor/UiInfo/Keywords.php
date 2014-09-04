<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo_Keywords
{
    /**
     * @var AbstractConfigurationEntity
     */
    private $_entity;

    public function __construct(AbstractConfigurationEntity $entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        $keywords = array();
        if ($this->_entity->keywordsEn) {
            $keywords['en'] = $this->_entity->keywordsEn;
        }
        if ($this->_entity->keywordsNl) {
            $keywords['nl'] = $this->_entity->keywordsNl;
        }
        if (empty($keywords)) {
            return $rootElement;
        }
        
        if (!isset($rootElement['md:Extensions'])) {
            $rootElement['md:Extensions'] = array();
        }
        if (!isset($rootElement['md:Extensions']['mdui:UIInfo'])) {
            $rootElement['md:Extensions']['mdui:UIInfo'] = array(0=>array());
        }
        $uiInfo = &$rootElement['md:Extensions']['mdui:UIInfo'][0];
        if (!isset($uiInfo['mdui:Keywords'])) {
            $uiInfo['mdui:Keywords'] = array();
        }

        foreach ($keywords as $langCode => $value) {
            if (trim($value)==='') {
                continue;
            }

            $uiInfo['mdui:Keywords'][] = array(
                array(
                    '_xml:lang' => $langCode,
                    '__v' => $value,
                ),
            );
        }
        return $rootElement;
    }
}