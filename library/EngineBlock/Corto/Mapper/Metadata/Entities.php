<?php

class EngineBlock_Corto_Mapper_Metadata_Entities
{
    private $_entities;

    public function __construct(array $entities)
    {
        $this->_entities = $entities;
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

        $rootElement['md:EntityDescriptor'] = array();
        foreach ($this->_entities as $entity) {
            $rootElement['md:EntityDescriptor'][] = $this->_mapEntity($entity);
        }
        return $rootElement;
    }

    protected function _mapEntity(array $entity)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity($entity);
        return $mapper->map();
    }
}