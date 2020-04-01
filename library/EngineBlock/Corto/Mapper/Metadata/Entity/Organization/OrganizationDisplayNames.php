<?php

/**
 * Copyright 2010 SURFnet B.V.
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

class EngineBlock_Corto_Mapper_Metadata_Entity_Organization_OrganizationDisplayNames
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
        $organizationDisplayNames = array();
        if ($this->_entity->organizationNl) {
            $organizationDisplayNames['nl'] = $this->_entity->organizationNl->displayName;
        }
        if ($this->_entity->organizationEn) {
            $organizationDisplayNames['en'] = $this->_entity->organizationEn->displayName;
        }
        if ($this->_entity->organizationPt) {
            $organizationDisplayNames['pt'] = $this->_entity->organizationPt->displayName;
        }
        if (empty($organizationDisplayNames)) {
            return $rootElement;
        }

        $rootElement['md:OrganizationDisplayName'] = array();
        foreach($organizationDisplayNames as $languageCode => $value) {
            $rootElement['md:OrganizationDisplayName'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xml:lang' => $languageCode,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $value
            );
        }
        return $rootElement;
    }
}
