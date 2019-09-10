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

use EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer as ServiceReplacer;
use OpenConext\EngineBlock\Metadata\Entity\IdentityProvider;
use OpenConext\EngineBlock\Metadata\Service;
use SAML2\Constants;

class EngineBlock_Test_ServiceReplacerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var IdentityProvider
     */
    private $entity;

    /**
     * @var IdentityProvider
     */
    private $proxyEntity;

    public function setUp()
    {
        $this->entity = new IdentityProvider('https://sp.example.edu');
        $this->entity->singleSignOnServices[] = new Service(Constants::BINDING_HTTP_REDIRECT, 'redirectlocation');

        $this->proxyEntity = new IdentityProvider('https://proxy.example.edu');
        $this->proxyEntity->singleSignOnServices[] = new Service('proxyRedirectLocation', Constants::BINDING_HTTP_REDIRECT);
        $this->proxyEntity->singleSignOnServices[] = new Service('proxyPostLocation', Constants::BINDING_HTTP_POST);
    }

    public function testServicesAreReplaced()
    {
        $replacer = new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
        $replacer->replace($this->entity, 'newLocation');

        $expectedBinding = array(
            new Service('newLocation', Constants::BINDING_HTTP_REDIRECT),
            new Service('newLocation', Constants::BINDING_HTTP_POST),
        );
        $this->assertEquals($expectedBinding, $this->entity->singleSignOnServices);
    }

    public function testServicesAreAdded()
    {
        $replacer = new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
        unset($this->entity->singleSignOnServices);
        $replacer->replace($this->entity, 'newLocation');

        $expectedBinding = array(
            new Service('newLocation', Constants::BINDING_HTTP_REDIRECT),
            new Service('newLocation', Constants::BINDING_HTTP_POST),
        );
        $this->assertEquals($expectedBinding, $this->entity->singleSignOnServices);
    }

    public function testMissingServiceMetadataThrowsException()
    {
        $this->expectException(EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception::class);
        $this->expectExceptionMessage('No service "singleSignOnServices" is configured in EngineBlock metadata');

        unset($this->proxyEntity->singleSignOnServices);
        new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);

    }

    public function testMissingServiceMetadataIsAllowedWhenOptional()
    {
        unset($this->proxyEntity->singleSignOnServices);
        new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::OPTIONAL);
    }

    public function testInvalidServiceMetadataThrowsException()
    {
        $this->expectException(EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception::class);
        $this->expectExceptionMessage('Service "SingleSignOnService" in EngineBlock metadata is not an array');

        $this->proxyEntity->singleSignOnServices = false;
        new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
    }

    public function testMissingServiceBindingMetadataThrowsException()
    {
        $this->expectException(EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception::class);
        $this->expectExceptionMessage('Service "SingleSignOnService" configured without a Binding in EngineBlock metadata');

        unset($this->proxyEntity->singleSignOnServices[0]->binding);
        new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
    }

    public function testInvalidServiceBindingMetadataThrowsException()
    {
        $this->expectException(EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception::class);
        $this->expectExceptionMessage('Service "SingleSignOnService" has an invalid binding "foo" configured in EngineBlock metadata');

        $this->proxyEntity->singleSignOnServices[0]->binding = 'foo';
        new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
    }

    public function testNoValidServiceBindingsFoundInMetadataThrowsException()
    {
        $this->expectException(EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception::class);
        $this->expectExceptionMessage('No "singleSignOnServices" service bindings configured in EngineBlock metadata');

        $this->proxyEntity->singleSignOnServices = array();
        new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
    }

    public function testNoValidServiceBindingsFoundInMetadataIsAllowedWhenOptional()
    {
        $this->proxyEntity->singleSignOnServices = array();
        $replacer = new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::OPTIONAL);
        $replacer->replace($this->entity, 'newLocation');
    }
}
