<?php

use EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer as ServiceReplacer;

class EngineBlock_Test_ServiceReplacerTest
    extends PHPUnit_Framework_TestCase
{
    private $entity;
    private $proxyEntity;

    public function setUp()
    {
        $this->entity = array(
            'SingleSignOn' => array(
                array(
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    'Location' => 'redirectLocation'
                )
            )
        );

        $this->proxyEntity = array(
            'SingleSignOn' => array(
                array(
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    'Location' => 'proxyRedirectLocation'
                ),
                array(
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    'Location' => 'proxyPostLocation'
                )
            )
        );
    }

    public function testServicesAreReplaced()
    {
        $replacer = new ServiceReplacer($this->proxyEntity, 'SingleSignOn', ServiceReplacer::REQUIRED);
        $replacer->replace($this->entity, 'newLocation');

        $expectedBinding = array(
            array(
                'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'Location' => 'newLocation'
            ),
            array(
                'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                'Location' => 'newLocation'
            )
        );
        $this->assertEquals($expectedBinding, $this->entity['SingleSignOn']);
    }

    public function testServicesAreAdded()
    {
        $replacer = new ServiceReplacer($this->proxyEntity, 'SingleSignOn', ServiceReplacer::REQUIRED);
        unset($this->entity['SingleSignOn']);
        $replacer->replace($this->entity, 'newLocation');

        $expectedBinding = array(
            array(
                'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                'Location' => 'newLocation'
            ),
            array(
                'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                'Location' => 'newLocation'
            )
        );
        $this->assertEquals($expectedBinding, $this->entity['SingleSignOn']);
    }

    /**
     * @expectedException EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception
     * @expectedExceptionMessage No service 'SingleSignOn' is configured in EngineBlock metadata
     */
    public function testMissingServiceMetadataThrowsException()
    {
        unset($this->proxyEntity['SingleSignOn']);
        new ServiceReplacer($this->proxyEntity, 'SingleSignOn', ServiceReplacer::REQUIRED);

    }

    public function testMissingServiceMetadataIsAllowedWhenOptional()
    {
        unset($this->proxyEntity['SingleSignOn']);
        new ServiceReplacer($this->proxyEntity, 'SingleSignOn', ServiceReplacer::OPTIONAL);
    }
    /**
     * @expectedException EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception
     * @expectedExceptionMessage Service 'SingleSignOn' in EngineBlock metadata is not an array
     */
    public function testInvalidServiceMetadataThrowsException()
    {
        $this->proxyEntity['SingleSignOn'] = false;
        new ServiceReplacer($this->proxyEntity, 'SingleSignOn', ServiceReplacer::REQUIRED);
    }

    /**
     * @expectedException EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception
     * @expectedExceptionMessage Service 'SingleSignOn' configured without a Binding in EngineBlock metadata
     */
    public function testMissingServiceBindingMetadataThrowsException()
    {
        unset($this->proxyEntity['SingleSignOn'][0]['Binding']);
        new ServiceReplacer($this->proxyEntity, 'SingleSignOn', ServiceReplacer::REQUIRED);
    }

    /**
     * @expectedException EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception
     * @expectedExceptionMessage Service 'SingleSignOn' has an invalid binding 'foo' configured in EngineBlock metadata
     */
    public function testInvalidServiceBindingMetadataThrowsException()
    {
        $this->proxyEntity['SingleSignOn'][0]['Binding'] = 'foo';
        new ServiceReplacer($this->proxyEntity, 'SingleSignOn', ServiceReplacer::REQUIRED);
    }

    /**
     * @expectedException EngineBlock_Corto_Module_Service_Metadata_ServiceReplacer_Exception
     * @expectedExceptionMessage No 'SingleSignOn' service bindings configured in EngineBlock metadata
     */
    public function testNoValidServiceBindingsFoundInMetadataThrowsException()
    {
        $this->proxyEntity['SingleSignOn'] = array();
        new ServiceReplacer($this->proxyEntity, 'SingleSignOn', ServiceReplacer::REQUIRED);
    }

    public function testNoValidServiceBindingsFoundInMetadataIsAllowedWhenOptional()
    {
        $this->proxyEntity['SingleSignOn'] = array();
        $replacer = new ServiceReplacer($this->proxyEntity, 'SingleSignOn', ServiceReplacer::OPTIONAL);
        $replacer->replace($this->entity, 'newLocation');
    }
}