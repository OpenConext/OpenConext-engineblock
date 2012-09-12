<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AssertionConsumerServices
{
    private $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        // Set consumer service on SP
        if (!isset($this->_entity['AssertionConsumerServices'])) {
            return $rootElement;
        }
        $rootElement['md:AssertionConsumerService'] = array();
        foreach ($this->_entity['AssertionConsumerServices'] as $index => $acs) {
            $rootElement['md:AssertionConsumerService'][] = array(
                '_Binding'  => $acs['Binding'],
                '_Location' => $acs['Location'],
                '_index' => $index,
            );
        }
        return $rootElement;
    }
}