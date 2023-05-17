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

use OpenConext\EngineBlock\Metadata\AttributeReleasePolicy;
use PHPUnit\Framework\TestCase;

class EngineBlock_Test_Arp_AttributeReleasePolicyEnforcer extends TestCase
{
    /**
     * @var EngineBlock_Arp_AttributeReleasePolicyEnforcer
     */
    protected $_arpEnforcer;

    public function setUp(): void
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
