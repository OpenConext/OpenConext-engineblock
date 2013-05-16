<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_SingleLogoutService
{
    /** @var  array */
    private $_entity;

    public function __construct(array $entity)
    {
        $this->_entity = $entity;
    }

    /**
     * @param array $rootElement
     * @return array
     */
    public function mapTo(array $rootElement)
    {

        if (isset($this->_entity['SingleLogoutService']['Binding']) &&
            isset($this->_entity['SingleLogoutService']['Location'])) {
            $rootElement['md:SingleLogoutService'] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Binding' => $this->_entity['SingleLogoutService']['Binding'],
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Location' => $this->_entity['SingleLogoutService']['Location']
            );
        }

        return $rootElement;
    }

}