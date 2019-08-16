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
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;

class EngineBlock_Corto_Mapper_Metadata_Entity_IdpSsoDescriptor extends EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor
{
    /**
     * @var AbstractRole
     */
    protected $_entity;

    public function __construct(AbstractRole $entity)
    {
        $this->_entity = $entity;
    }

    public function mapTo(array $rootElement)
    {
        if (!$this->_entity instanceof IdentityProvider) {
            return $rootElement;
        }

        $rootElement['md:IDPSSODescriptor'] = array(
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'protocolSupportEnumeration' => "urn:oasis:names:tc:SAML:2.0:protocol",
        );

        $rootElement['md:IDPSSODescriptor'] = $this->_mapScope($rootElement['md:IDPSSODescriptor']);
        $rootElement['md:IDPSSODescriptor'] = $this->_mapUiInfo($rootElement['md:IDPSSODescriptor']);
        $rootElement['md:IDPSSODescriptor'] = $this->_mapCertificates($rootElement['md:IDPSSODescriptor']);
        $rootElement['md:IDPSSODescriptor'] = $this->_mapSingleLogoutService($rootElement['md:IDPSSODescriptor']);
        $rootElement['md:IDPSSODescriptor'] = $this->_mapNameIdFormats($rootElement['md:IDPSSODescriptor']);
        $rootElement['md:IDPSSODescriptor'] = $this->_mapSingleSignOnService($rootElement['md:IDPSSODescriptor']);

        return $rootElement;
    }

    protected function _mapSingleSignOnService(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_IdpSsoDescriptor_SingleSignOnService($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapScope(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_IdpSsoDescriptor_Scope($this->_entity);
        return $mapper->mapTo($rootElement);
    }
}
