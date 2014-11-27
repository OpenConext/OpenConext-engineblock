<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;
use OpenConext\Component\EngineBlockMetadata\Entity\ServiceProviderEntity;

class EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AssertionConsumerServices
{
    /**
     * @var ServiceProviderEntity
     */
    private $_entity;

    public function __construct(ServiceProviderEntity $entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        // Set consumer service on SP
        if (!empty($this->_entity->assertionConsumerServices)) {
            return $rootElement;
        }

        $rootElement['md:AssertionConsumerService'] = array();
        foreach ($this->_entity->assertionConsumerServices as $index => $acs) {
            $acsElement = array(
                '_Binding'  => $acs->binding,
                '_Location' => $acs->location,
                '_index'    => $index,
            );
            if (is_bool($acs->isDefault)) {
                $acsElement['_isDefault'] = $acs->isDefault;
            }
            $rootElement['md:AssertionConsumerService'][] = $acsElement;
        }
        return $rootElement;
    }
}