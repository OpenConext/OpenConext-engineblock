<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractRole;

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_NameIdFormat
{
    /**
     * @var AbstractRole
     */
    private $_entity;

    public function __construct(AbstractRole $entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (empty($this->_entity->nameIdFormat) && empty($this->_entity->supportedNameIdFormats)) {
            return $rootElement;
        }

        $rootElement['md:NameIDFormat'] = array();

        if (empty($this->_entity->supportedNameIdFormats)) {
            $rootElement['md:NameIDFormat'] = array(
                array('__v' => $this->_entity->nameIdFormat)
            );
            return $rootElement;
        }

        foreach ($this->_entity->supportedNameIdFormats as $nameIdFormat) {
            $rootElement['md:NameIDFormat'][] = array('__v' => $nameIdFormat);
        }
        return $rootElement;
    }
}
