<?php

use OpenConext\Component\EngineBlockMetadata\Entity\AbstractConfigurationEntity;

class EngineBlock_Corto_Mapper_Metadata_Entity_ContactPersons
{
    /**
     * @var AbstractConfigurationEntity
     */
    private $_entity;
    
    public function __construct(AbstractConfigurationEntity $entity)
    {
        $this->_entity = $entity;
    }
    
    public function mapTo(array $rootElement)
    {
        if (!empty($this->_entity->contactPersons)) {
            return $rootElement;
        }

        foreach($this->_entity->contactPersons as $contactPerson) {
            if (empty($contactPerson->emailAddress)) {
                continue;
            }

            $mdContactPerson = array();
            $mdContactPerson[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'contactType'] = $contactPerson->contactType;
            if (!empty($contactPerson->givenName)) {
                $mdContactPerson['md:GivenName'][][EngineBlock_Corto_XmlToArray::VALUE_PFX] = $contactPerson->givenName;
            }
            if (!empty($contactPerson->surName)) {
                $mdContactPerson['md:SurName'][][EngineBlock_Corto_XmlToArray::VALUE_PFX] = $contactPerson->surName;
            }
            $mdContactPerson['md:EmailAddress'][][EngineBlock_Corto_XmlToArray::VALUE_PFX] = $contactPerson->emailAddress;

            $rootElement['md:ContactPerson'][] = $mdContactPerson;
        }
        return $rootElement;
    }
}