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

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;

class EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AssertionConsumerServices
{
    /**
     * @var ServiceProvider
     */
    private $_entity;

    public function __construct(ServiceProvider $entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        // Set consumer service on SP
        if (empty($this->_entity->assertionConsumerServices)) {
            return $rootElement;
        }

        $rootElement['md:AssertionConsumerService'] = array();
        foreach ($this->_entity->assertionConsumerServices as $index => $acs) {
            $acsElement = array(
                '_Binding'  => $acs->binding,
                '_Location' => $acs->location,
                '_index'    => $acs->serviceIndex,
            );
            if (is_bool($acs->isDefault)) {
                $acsElement['_isDefault'] = $acs->isDefault;
            }
            $rootElement['md:AssertionConsumerService'][] = $acsElement;
        }
        return $rootElement;
    }
}
