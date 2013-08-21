<?php

use EngineBlock_Corto_Module_Service_Metadata_BindingsReplacer as BindingsReplacer;

class BindingsReplacerTest
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

    public function testBindingsAreReplaced()
    {
        $replacer = new BindingsReplacer($this->proxyEntity, 'SingleSignOn', BindingsReplacer::REQUIRED);
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

    public function testBindingsAreAdded()
    {
        $replacer = new BindingsReplacer($this->proxyEntity, 'SingleSignOn', BindingsReplacer::REQUIRED);
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
     * @expectedException EngineBlock_Corto_Module_Service_Metadata_BindingsReplacer_Exception
     * @expectedExceptionMessage No service 'SingleSignOn' is configured in EngineBlock metadata
     */
    public function testMissingServiceMetadataThrowsException()
    {
        unset($this->proxyEntity['SingleSignOn']);
        new BindingsReplacer($this->proxyEntity, 'SingleSignOn', BindingsReplacer::REQUIRED);

    }

    /**
     * @expectedException EngineBlock_Corto_Module_Service_Metadata_BindingsReplacer_Exception
     * @expectedExceptionMessage Service 'SingleSignOn' in EngineBlock metadata is not an array
     */
    public function testInvalidServiceMetadataThrowsException()
    {
        $this->proxyEntity['SingleSignOn'] = false;
        new BindingsReplacer($this->proxyEntity, 'SingleSignOn', BindingsReplacer::REQUIRED);
    }

    /**
     * @expectedException EngineBlock_Corto_Module_Service_Metadata_BindingsReplacer_Exception
     * @expectedExceptionMessage Service 'SingleSignOn' configured without a Binding in EngineBlock metadata
     */
    public function testMissingServiceBindingInMetadataThrowsException()
    {
        unset($this->proxyEntity['SingleSignOn'][0]['Binding']);
        new BindingsReplacer($this->proxyEntity, 'SingleSignOn', BindingsReplacer::REQUIRED);
    }

    public function testMissingBindingsInMetadataIsAllowedWhenOptional()
    {
        unset($this->proxyEntity['SingleSignOn']);
        $replacer = new BindingsReplacer($this->proxyEntity, 'SingleSignOn', BindingsReplacer::OPTIONAL);
        $replacer->replace($this->entity, 'newLocation');
    }

    /**
     * @expectedException EngineBlock_Corto_Module_Service_Metadata_BindingsReplacer_Exception
     * @expectedExceptionMessage Service 'SingleSignOn' has an invalid binding 'foo' configured in EngineBlock metadata
     */
    public function testInvalidServiceBindingInMetadataThrowsException()
    {
        $this->proxyEntity['SingleSignOn'][0]['Binding'] = 'foo';
        new BindingsReplacer($this->proxyEntity, 'SingleSignOn', BindingsReplacer::REQUIRED);
    }

    /**
     * @expectedException EngineBlock_Corto_Module_Service_Metadata_BindingsReplacer_Exception
     * @expectedExceptionMessage No 'SingleSignOn' bindings configured in EngineBlock metadata
     */
    public function testNoValidBindingsFoundInMetadataThrowsException()
    {
        $this->proxyEntity['SingleSignOn'] = array();
        new BindingsReplacer($this->proxyEntity, 'SingleSignOn', BindingsReplacer::REQUIRED);
    }

    public function testNoValidBindingsFoundInMetadataIsAllowedWhenOptional()
    {
        $this->proxyEntity['SingleSignOn'] = array();
        $replacer = new BindingsReplacer($this->proxyEntity, 'SingleSignOn', BindingsReplacer::OPTIONAL);
        $replacer->replace($this->entity, 'newLocation');
    }
}