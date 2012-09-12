<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_NameIdFormat
{
    private $_entity;

    public function __construct($entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (empty($this->_entity['NameIDFormat']) && empty($this->_entity['NameIDFormats'])) {
            return $rootElement;
        }

        $rootElement['md:NameIDFormat'] = array();
        if (!empty($this->_entity['NameIDFormats'])) {
            foreach ($this->_entity['NameIDFormats'] as $nameIdFormat) {
                $rootElement['md:NameIDFormat'][] = array('__v' => $nameIdFormat);
            }
        }
        else {
            $rootElement['md:NameIDFormat'] = array(array('__v' => $this->_entity['NameIDFormat']));
        }
        return $rootElement;
    }
}