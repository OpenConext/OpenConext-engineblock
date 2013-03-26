<?php

class EngineBlock_Corto_Mapper_Metadata_Entity_ContactPersons
{
    private $_entity;
    
    public function __construct($entity)
    {
        $this->_entity = $entity;
    }
    
    public function mapTo(array $rootElement)
    {
        if (!array_key_exists('ContactPersons', $this->_entity)) {
            return $rootElement;
        }

        foreach($this->_entity['ContactPersons'] as $contactPerson) {
            if (empty($contactPerson['EmailAddress'])) {
                continue;
            }

            $mdContactPerson = array();
            $mdContactPerson[EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'contactType'] = $contactPerson['ContactType'];
            $mdContactPerson['md:EmailAddress'][][EngineBlock_Corto_XmlToArray::VALUE_PFX] = $contactPerson['EmailAddress'];
            if (!empty($contactPerson['GivenName'])) {
                $mdContactPerson['md:GivenName'][][EngineBlock_Corto_XmlToArray::VALUE_PFX] = $contactPerson['GivenName'];
            }
            if (!empty($contactPerson['SurName'])) {
                $mdContactPerson['md:SurName'][][EngineBlock_Corto_XmlToArray::VALUE_PFX] = $contactPerson['SurName'];
            }

            $rootElement['md:ContactPerson'][] = $mdContactPerson;
        }
        return $rootElement;
    }
}