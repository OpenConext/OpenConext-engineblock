<?php

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo_DisplayName
{
    /**
     * @var AbstractRole
     */
    private $_entity;

    /**
     * @param AbstractRole $entity
     */
    public function __construct(AbstractRole $entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        $displayNames = array();
        if ($this->_entity->displayNameNl) {
            $displayNames['nl'] = $this->_entity->displayNameNl;
        }
        if ($this->_entity->displayNameEn) {
            $displayNames['en'] = $this->_entity->displayNameEn;
        }
        if ($this->_entity->displayNamePt) {
            $displayNames['pt'] = $this->_entity->displayNamePt;
        }
        if (empty($displayNames)) {
            return $rootElement;
        }

        if (!isset($rootElement['md:Extensions'])) {
            $rootElement['md:Extensions'] = array();
        }
        if (!isset($rootElement['md:Extensions']['mdui:UIInfo'])) {
            $rootElement['md:Extensions']['mdui:UIInfo'] = array(0=>array());
        }

        foreach($displayNames as $languageCode => $value) {
            if(empty($value)) {
                continue;
            }

            $rootElement['md:Extensions']['mdui:UIInfo'][0]['mdui:DisplayName'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $languageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $value
            );
        }
        return $rootElement;
    }
}
