<?php

use EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer as ServiceReplacer;
use OpenConext\Component\EngineBlockMetadata\Entity\IdentityProviderEntity;
use OpenConext\Component\EngineBlockMetadata\Service;

class EngineBlock_Test_ServiceReplacerTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @var IdentityProviderEntity
     */
    private $entity;

    /**
     * @var IdentityProviderEntity
     */
    private $proxyEntity;

    public function setUp()
    {
        $this->entity = new IdentityProviderEntity('https://sp.example.edu');
        $this->entity->singleSignOnServices[] = new Service(SAML2_Const::BINDING_HTTP_REDIRECT, 'redirectlocation');

        $this->proxyEntity = new IdentityProviderEntity('https://proxy.example.edu');
        $this->proxyEntity->singleSignOnServices[] = new Service('proxyRedirectLocation', SAML2_Const::BINDING_HTTP_REDIRECT);
        $this->proxyEntity->singleSignOnServices[] = new Service('proxyPostLocation', SAML2_Const::BINDING_HTTP_POST);
    }

    public function testServicesAreReplaced()
    {
        $replacer = new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
        $replacer->replace($this->entity, 'newLocation');

        $expectedBinding = array(
            new Service('newLocation', SAML2_Const::BINDING_HTTP_REDIRECT),
            new Service('newLocation', SAML2_Const::BINDING_HTTP_POST),
        );
        $this->assertEquals($expectedBinding, $this->entity->singleSignOnServices);
    }

    public function testServicesAreAdded()
    {
        $replacer = new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
        unset($this->entity->singleSignOnServices);
        $replacer->replace($this->entity, 'newLocation');

        $expectedBinding = array(
            new Service('newLocation', SAML2_Const::BINDING_HTTP_REDIRECT),
            new Service('newLocation', SAML2_Const::BINDING_HTTP_POST),
        );
        $this->assertEquals($expectedBinding, $this->entity->singleSignOnServices);
    }

    /**
     * @expectedException EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception
     * @expectedExceptionMessage No service 'singleSignOnServices' is configured in EngineBlock metadata
     */
    public function testMissingServiceMetadataThrowsException()
    {
        unset($this->proxyEntity->singleSignOnServices);
        new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);

    }

    public function testMissingServiceMetadataIsAllowedWhenOptional()
    {
        unset($this->proxyEntity->singleSignOnServices);
        new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::OPTIONAL);
    }
    /**
     * @expectedException EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception
     * @expectedExceptionMessage Service 'SingleSignOnService' in EngineBlock metadata is not an array
     */
    public function testInvalidServiceMetadataThrowsException()
    {
        $this->proxyEntity->singleSignOnServices = false;
        new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
    }

    /**
     * @expectedException EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception
     * @expectedExceptionMessage Service 'SingleSignOnService' configured without a Binding in EngineBlock metadata
     */
    public function testMissingServiceBindingMetadataThrowsException()
    {
        unset($this->proxyEntity->singleSignOnServices[0]->binding);
        new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
    }

    /**
     * @expectedException EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception
     * @expectedExceptionMessage Service 'SingleSignOnService' has an invalid binding 'foo' configured in EngineBlock metadata
     */
    public function testInvalidServiceBindingMetadataThrowsException()
    {
        $this->proxyEntity->singleSignOnServices[0]->binding = 'foo';
        new ServiceReplacer($this->proxyEntity, 'SingleSignOnService', ServiceReplacer::REQUIRED);
    }

    /**
     * @expectedException EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception
     * @expectedExceptionMessage No 'singleSignOnServices' service bindings configured in EngineBlock metadata
     */
    public function testNoValidServiceBindingsFoundInMetadataThrowsException()
    {
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