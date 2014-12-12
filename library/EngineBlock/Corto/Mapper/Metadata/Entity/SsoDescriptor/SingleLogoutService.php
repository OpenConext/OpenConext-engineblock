<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_SingleLogoutService
{
    /**
     * @var AbstractConfigurationEntity
     */
    private $_entity;

    public function __construct(AbstractConfigurationEntity $entity)
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
        if (empty($this->_entity->singleLogoutServices)) {
            return $rootElement;
        }

        foreach ($this->_entity->singleLogoutServices as $service) {
            if ($service->binding && $service->location) {
                if (!isset($rootElement['md:SingleLogoutService'])) {
                    $rootElement['md:SingleLogoutService'] = array();
                }

                $rootElement['md:SingleLogoutService'][] = array(
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Binding'  => $service->binding,
                    EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Location' => $service->location,
                );
            }
        }
        return $rootElement;
    }

}