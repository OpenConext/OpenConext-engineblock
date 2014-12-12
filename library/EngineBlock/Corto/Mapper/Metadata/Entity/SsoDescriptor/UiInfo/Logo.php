<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_UiInfo_Logo
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
        if (!$this->_entity->logo) {
            return $rootElement;
        }

        if (!$this->_entity->logo->height || !$this->_entity->logo->width) {
            // @todo warn here!
            return $rootElement;
        }

        if (!isset($rootElement['md:Extensions'])) {
            $rootElement['md:Extensions'] = array();
        }
        if (!isset($rootElement['md:Extensions']['mdui:UIInfo'])) {
            $rootElement['md:Extensions']['mdui:UIInfo'] = array(0=>array());
        }
        $rootElement['md:Extensions']['mdui:UIInfo'][0]['mdui:Logo'] = array(
            array(
                '_height' => $this->_entity->logo->height,
                '_width'  => $this->_entity->logo->width,
                '__v'     => $this->_entity->logo->url,
            ),
        );
        return $rootElement;
    }
}