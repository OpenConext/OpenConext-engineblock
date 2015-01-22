<?php

use OpenConext\Component\EngineBlockMetadata\AttributeReleasePolicy;

class EngineBlock_Test_Arp_AttributeReleasePolicyEnforcer extends PHPUnit_Framework_TestCase
{
    /**
     * @var EngineBlock_Arp_AttributeReleasePolicyEnforcer
     */
    protected $_arpEnforcer;

    public function setup()
    {
        $this->_arpEnforcer = new EngineBlock_Arp_AttributeReleasePolicyEnforcer();
    }

    public function testEnforceNullArp()
    {
        $arp = null;

        $newAttributes = $this->_doEnforceArp($arp);

        $this->assertEquals($this->_responseAttributes(), $newAttributes);
    }

    public function testEnforceEmptyArp()
    {
        $arp = array();

        $newAttributes = $this->_doEnforceArp($arp);

        $this->assertEmpty($newAttributes, 'An empty ARP blocks all attributes');
    }

    public function testEnforceNotExactMatchArp()
    {
        $arp = array(
            'name' => array('Laura Wilkins')
        );
        $newAttributes = $this->_doEnforceArp($arp);
        $this->assertTrue(empty($newAttributes));
    }

    public function testEnforceExactMatchArp()
    {
        $arp = array(
            'name' => array('John Doe')
        );
        $newAttributes = $this->_doEnforceArp($arp);
        $this->assertEquals(count($newAttributes['name']), 1);
        $this->assertEquals($newAttributes['name'][0], 'John Doe');
    }

    public function testEnforceNotPrefixMatchArp()
    {
        $arp = array(
            'name' => array('Laura*')
        );
        $newAttributes = $this->_doEnforceArp($arp);
        $this->assertTrue(empty($newAttributes));
    }

    public function testEnforcePrefixMatchArp()
    {
        $arp = array(
            'name' => array('John*')
        );
        $newAttributes = $this->_doEnforceArp($arp);
        $this->assertEquals(count($newAttributes['name']), 1);
        $this->assertEquals($newAttributes['name'][0], 'John Doe');
    }

    public function testEnforceWildcardMatchArpMultipleValues()
    {
        $arp = array(
            'name' => array('*')
        );
        $responseAttributes = array(
            'name' => array('John Doe', 'Mark Benson')
        );
        $newAttributes = $this->_doEnforceArp($arp, $responseAttributes);
        $this->assertEquals($newAttributes['name'], $responseAttributes['name']);
    }

    public function testEnforcePrefixMatchArpMultipleValues()
    {
        $arp = array(
            'name' => array('John*'),
            'organization' => array('Surf*')
        );
        $responseAttributes = array(
            'name' => array('John Doe', 'John Johnson'),
            'organization' => array('SurfNet', 'Guest')
        );
        $newAttributes = $this->_doEnforceArp($arp, $responseAttributes);
        $this->assertEquals($newAttributes['name'], $responseAttributes['name']);

        $this->assertEquals(count($newAttributes['organization']), 1);
        $this->assertEquals($newAttributes['organization'][0], 'SurfNet');
    }

    protected function _doEnforceArp($arp, $responseAttributes = array())
    {
        $responseAttributes = empty($responseAttributes) ? $this->_responseAttributes() : $responseAttributes;
        return $this->_arpEnforcer->enforceArp(
            $arp === null ? null : new AttributeReleasePolicy($arp),
            $responseAttributes
        );
    }

    protected function _responseAttributes()
    {
        return array(
            'name' => array('John Doe'),
            'organization' => array('Surfnet')
        );
    }
}