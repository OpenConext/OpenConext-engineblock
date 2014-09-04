<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_NameIdFormat
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
        if (empty($this->_entity->nameIdFormat) && empty($this->_entity->nameIdFormats)) {
            return $rootElement;
        }

        $rootElement['md:NameIDFormat'] = array();

        if (empty($this->_entity->nameIdFormats)) {
            $rootElement['md:NameIDFormat'] = array(
                array('__v' => $this->_entity->nameIdFormat)
            );
            return $rootElement;
        }

        foreach ($this->_entity->nameIdFormats as $nameIdFormat) {
            $rootElement['md:NameIDFormat'][] = array('__v' => $nameIdFormat);
        }
        return $rootElement;
    }
}