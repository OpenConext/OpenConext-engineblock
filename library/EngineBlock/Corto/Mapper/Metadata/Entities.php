<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;

class EngineBlock_Corto_Mapper_Metadata_Entities
{
    private $_entities;
    private $_eduGain;

    /**
     * @param AbstractConfigurationEntity[] $entities
     * @param boolean$eduGain
     */
    public function __construct(array $entities, $eduGain)
    {
        $this->_entities = $entities;
        $this->_eduGain = $eduGain;
    }

    public function map()
    {
        return $this->mapTo(array());
    }

    public function mapTo(array $rootElement)
    {
        $rootElement[EngineBlock_Corto_XmlToArray::TAG_NAME_PFX] = 'md:EntitiesDescriptor';

        if (empty($this->_entities)) {
            return $rootElement;
        }

        if ($this->_eduGain) {
            $rootElement = $this->_mapMdRpi($rootElement);
        }

        $rootElement['md:EntityDescriptor'] = array();
        foreach ($this->_entities as $entity) {
            $rootElement['md:EntityDescriptor'][] = $this->_mapEntity($entity);
        }
        return $rootElement;
    }

    protected function _mapEntity(AbstractConfigurationEntity $entity)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity($entity, $this->_eduGain);
        return $mapper->map();
    }

    protected function _mapMdRpi(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_MdRpi_PublicationInfo();
        return $mapper->mapTo($rootElement);
    }
}