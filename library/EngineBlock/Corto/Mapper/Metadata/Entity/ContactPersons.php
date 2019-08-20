<?php

/**
 * Copyright 2014 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use OpenConext\EngineBlock\Metadata\Entity\AbstractRole;

class EngineBlock_Corto_Mapper_Metadata_Entity_ContactPersons
{
    /**
     * @var AbstractRole
     */
    private $_entity;

    public function __construct(AbstractRole $entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (empty($this->_entity->contactPersons)) {
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
