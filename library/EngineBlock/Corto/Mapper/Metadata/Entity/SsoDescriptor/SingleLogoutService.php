<?php

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;

class EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor_SingleLogoutService
{
    /**
     * @var AbstractRole
     */
    private $_entity;

    public function __construct(AbstractRole $entity)
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
        if (empty($this->_entity->singleLogoutService)) {
            return $rootElement;
        }

        $service = $this->_entity->singleLogoutService;

        if ($service->binding && $service->location) {
            if (!isset($rootElement['md:SingleLogoutService'])) {
                $rootElement['md:SingleLogoutService'] = array();
            }

            $rootElement['md:SingleLogoutService'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Binding'  => $service->binding,
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'Location' => $service->location,
            );
        }
        return $rootElement;
    }

}
