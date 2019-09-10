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

use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;

class EngineBlock_Corto_Mapper_Metadata_Entity_IdpSsoDescriptor_Scope
{
    /**
     * @var IdentityProvider
     */
    private $_entity;

    public function __construct(IdentityProvider $entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (empty($this->_entity->shibMdScopes)) {
            return $rootElement;
        }
        if (!isset($rootElement['md:Extensions'])) {
            $rootElement['md:Extensions'] = array();
        }
        foreach ($this->_entity->shibMdScopes as $scope) {
            $rootElement['md:Extensions']['shibmd:Scope'][] = array(
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'xmlns:shibmd' => 'urn:mace:shibboleth:metadata:1.0',
                EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'regexp' => $scope->regexp ? 'true' : 'false' ,
                EngineBlock_Corto_XmlToArray::VALUE_PFX => $scope->allowed,
            );
        }
        return $rootElement;
    }
}
