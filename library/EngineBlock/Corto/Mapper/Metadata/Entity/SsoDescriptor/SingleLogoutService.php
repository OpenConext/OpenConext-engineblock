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
     * Maps one or more single logout locations
     *
     * @param array $rootElement
     * @return array
     */
    public function mapTo(array $rootElement)
    {

        if (isset($this->_entity['SingleLogoutService'])) {
            foreach ($this->_entity['SingleLogoutService'] as $service) {
                if (isset($service['Binding']) && isset($service['Location'])) {
                    if (!isset($rootElement['md:SingleLogoutService'])) {
                        $rootElement['md:SingleLogoutService'] = array();
                    }

                    $rootElement['md:SingleLogoutService'][] = array(
                        EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Binding' => $service['Binding'],
                        EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Location' => $service['Location']
                    );
                }
            }
        }

        return $rootElement;
    }

}