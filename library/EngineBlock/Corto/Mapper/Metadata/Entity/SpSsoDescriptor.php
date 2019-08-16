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
use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;

class EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor extends EngineBlock_Corto_Mapper_Metadata_Entity_SsoDescriptor
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
        if (!$this->_entity instanceof ServiceProvider) {
            return $rootElement;
        }

        $rootElement['md:SPSSODescriptor'] = array(
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'protocolSupportEnumeration' => "urn:oasis:names:tc:SAML:2.0:protocol",
            EngineBlock_Corto_XmlToArray::ATTRIBUTE_PFX . 'WantAssertionsSigned' => 'true'
        );

        $rootElement['md:SPSSODescriptor'] = $this->_mapUiInfo($rootElement['md:SPSSODescriptor']);
        $rootElement['md:SPSSODescriptor'] = $this->_mapCertificates($rootElement['md:SPSSODescriptor']);
        $rootElement['md:SPSSODescriptor'] = $this->_mapSingleLogoutService($rootElement['md:SPSSODescriptor']);
        $rootElement['md:SPSSODescriptor'] = $this->_mapAssertionConsumerServices($rootElement['md:SPSSODescriptor']);
        $rootElement['md:SPSSODescriptor'] = $this->_mapAttributeConsumingService($rootElement['md:SPSSODescriptor']);

        return $rootElement;
    }

    protected function _mapAssertionConsumerServices(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AssertionConsumerServices($this->_entity);
        return $mapper->mapTo($rootElement);
    }

    protected function _mapAttributeConsumingService(array $rootElement)
    {
        $mapper = new EngineBlock_Corto_Mapper_Metadata_Entity_SpSsoDescriptor_AttributeConsumingService($this->_entity);
        return $mapper->mapTo($rootElement);
    }

}
