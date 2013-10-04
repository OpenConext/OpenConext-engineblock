<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_MdRpi
{
    private $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        $rootElement = $this->_RegistrationPolicy($rootElement);
        return $rootElement;
    }

    protected function _RegistrationPolicy(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_MdRpi_RegistrationPolicy($this->_entity);
        return $mapper->mapTo($rootElement);
    }

}