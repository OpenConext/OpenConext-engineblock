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

class EngineBlock_Corto_Mapper_Metadata_Entity_IdpSsoDescriptor_SingleSignOnService
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
        // Set SSO on IDP
        if (!isset($this->_entity->singleSignOnServices)) {
            return $rootElement;
        }

        $rootElement['md:SingleSignOnService'] = array();
        foreach($this->_entity->singleSignOnServices as $service) {
            $rootElement['md:SingleSignOnService'][] = array(
                '_Binding'  => $service->binding,
                '_Location' => $service->location,
            );
        }

        return $rootElement;
    }
}
