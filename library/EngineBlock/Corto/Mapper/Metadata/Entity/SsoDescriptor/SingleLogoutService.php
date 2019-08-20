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
