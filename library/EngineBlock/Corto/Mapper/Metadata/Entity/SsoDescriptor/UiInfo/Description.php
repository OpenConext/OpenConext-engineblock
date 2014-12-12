<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo_Description
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
        $descriptions = array();
        if (!empty($this->_entity->descriptionEn)) {
            $descriptions['nl'] = $this->_entity->descriptionEn;
        }
        if (!empty($this->_entity->descriptionNl)) {
            $descriptions['en'] = $this->_entity->descriptionEn;
        }
        if (empty($descriptions)) {
            return $rootElement;
        }

        if (!isset($rootElement['md:Extensions'])) {
            $rootElement['md:Extensions'] = array();
        }
        if (!isset($rootElement['md:Extensions']['mdui:UIInfo'])) {
            $rootElement['md:Extensions']['mdui:UIInfo'] = array(0=>array());
        }

        foreach($descriptions as $languageCode => $value) {
            if(empty($value)) {
                continue;
            }

            $rootElement['md:Extensions']['mdui:UIInfo'][0]['mdui:Description'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $languageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $value
            );
        }
        return $rootElement;
    }
}