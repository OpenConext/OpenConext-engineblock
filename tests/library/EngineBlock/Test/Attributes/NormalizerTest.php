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

class EngineBlock_Test_Attributes_NormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testNormalizeUnnecessary()
    {
        $attributes = array(
            'attrib' => array(
                'val1',
                'val2'
            ),
        );
        $definition = array(
            'attrib' => array(),
        );
        $normalized = $this->_normalize($attributes, $definition);
        $this->assertEquals($attributes, $normalized, "No normalization required doesn't affect the attributes");
    }

    public function testNormalizeSimple()
    {
        $attributes = array(
            'attrib-alias' => array(
                'val1',
                'val2'
            ),
        );
        $definition = array(
            'attrib-alias' => 'attrib',
            'attrib' => array(),
        );
        $normalized = $this->_normalize($attributes, $definition);
        $this->assertEquals(array(
            'attrib' => array(
                'val1',
                'val2',
            ),
        ), $normalized, "Simple aliasing works");
    }

    public function testNormalizeMultilevel()
    {
        $attributes = array(
            'attrib-alias' => array(
                'val1',
                'val2'
            ),
        );
        $definition = array(
            'attrib-alias' => 'attrib-alias2',
            'attrib-alias2' => 'attrib',
            'attrib' => array(),
        );
        $normalized = $this->_normalize($attributes, $definition);
        $this->assertEquals(array(
                'attrib' => array(
                    'val1',
                    'val2',
                ),
            ), $normalized, "Simple aliasing works");
    }

    public function testNormalizeIgnoresAttributesNotInDefinition()
    {
        $attributes = array(
            'attrib' => array(
                'val1',
                'val2'
            ),
        );
        $definition = array();
        $normalized = $this->_normalize($attributes, $definition);
        $this->assertEquals($attributes, $normalized, "Unknown attributes are untouched after normalization");
    }

    public function testNormalizeWarnsOnFaultyDefinition()
    {
        $attributes = array(
            'attrib-a' => array(
                'value'
            ),
        );
        $definition = array(
            'attrib-a' => 'attrib-b',
        );

        $normalized = $this->_normalize($attributes, $definition);

        $this->assertEquals(
            array(
                'attrib-b' => array('value')
            ),
            $normalized,
            'An attribute with a broken aliasing will not be omitted'
        );
        $this->assertNotEmpty($this->_messageRecorder->messages, 'Broken aliasing sets a log event');
    }

    public function testNormalizeWarnOnConflict()
    {
        $originalAttributes = array(
            'urn:mace:dir:attribute-def:commonName' => array(
                'John Doe',
                'Alternate Common Name'
            ),
            'urn:oid:2.5.4.3' => array(
                'John Doe'
            ),
        );

        $definition = array(
            'urn:oid:2.5.4.3' => 'urn:mace:dir:attribute-def:cn',
            'urn:mace:dir:attribute-def:commonName' => 'urn:mace:dir:attribute-def:cn',
            'urn:mace:dir:attribute-def:cn' => array(
                'Description' => array(
                    'en' => 'your full name',
                    'nl' => 'volledige persoonsnaam'
                ),
                'Name' => array(
                    'en' => 'Full Name',
                    'nl' => 'Volledige persoonsnaam'
                )
            )
        );

        $normalized = $this->_normalize($originalAttributes, $definition);

        $this->assertArrayNotHasKey('urn:oid:2.5.4.3', $normalized, "OID attribute is normalized to non-oid variants");
        $this->assertEquals(
            array('John Doe'),
            $normalized['urn:mace:dir:attribute-def:cn'],
            'Last attribute value wins in normalization'
        );
        $this->assertNotEmpty($this->_messageRecorder->messages, "Conflict in attribute normalization leads to a log event");
    }

    public function testNormalizationCircularDependency()
    {
        $attributes = array(
            'a' => array( 'val' ),
        );
        $definition = array(
            'a' => 'b',
            'b' => 'c',
            'c' => 'a',
            'd' => array(),
        );

        $normalized = $this->_normalize($attributes, $definition);

        $this->assertEquals($attributes, $normalized, "Don't normalize on circular dependency");
        $this->assertNotEmpty($this->_messageRecorder->messages, "Log an event on a circular dependency");
    }

    public function testDenormalizeUnnecessary()
    {
        $attributes = array(
            'attrib' => array(
                'val1',
                'val2'
            ),
        );
        $definition = array(
            'attrib' => array(),
        );
        $denormalized = $this->_denormalize($attributes, $definition);
        $this->assertEquals($attributes, $denormalized, "No normalization required doesn't affect the attributes");
    }

    public function testDenormalizeSimple()
    {
        $attributes = array(
            'attrib' => array(
                'val1',
                'val2'
            ),
        );
        $definition = array(
            'attrib' => array(),
            'a' => 'attrib',
        );
        $denormalized = $this->_denormalize($attributes, $definition);
        $this->assertEquals(
            array(
                'attrib' => array('val1', 'val2'),
                'a' => array('val1', 'val2'),
            ),
            $denormalized,
            "No normalization required doesn't affect the attributes"
        );
    }

    public function testDenormalizeOfAlias()
    {
        $attributes = array(
            'attrib' => array(
                'val1',
                'val2'
            ),
        );
        $definition = array(
            'attrib' => 'a',
            'a' => array(),
        );
        $denormalized = $this->_denormalize($attributes, $definition);
        $this->assertEquals(
            array(
                'attrib' => array('val1', 'val2'),
            ),
            $denormalized,
            "Denormalization of an alias (which should never occur) will lead to no denormalization"
        );
    }

    public function testDenormalizeMultilevel()
    {
        $attributes = array(
            'attrib' => array(
                'val1',
                'val2'
            ),
        );
        $definition = array(
            'a' => 'b',
            'b' => 'attrib',
            'attrib' => array(),
        );
        $denormalized = $this->_denormalize($attributes, $definition);
        $this->assertEquals(
            array(
                'attrib' => array('val1', 'val2'),
                'a'      => array('val1', 'val2'),
                'b'      => array('val1', 'val2'),
            ),
            $denormalized,
            "Denormalization with multiple levels of aliasing"
        );
    }

    public function testDenormalizeDoesNotBreakOnUndefinedAttributes()
    {
        $attributes = array(
            'knownAttribute1' => array('val1', 'val2'),
            'unknownAttribute' => array('val1', 'val2'),
            'knownAttribute2' => array('val1', 'val2'),
        );
        $definition = array(
            'knownAttribute1' => '',
            'knownAttribute2' => ''
        );
        $denormalized = $this->_denormalize($attributes, $definition);

        $this->assertEquals(
            array(
                'knownAttribute1' => array('val1', 'val2'),
                'unknownAttribute' => array('val1', 'val2'),
                'knownAttribute2' => array('val1', 'val2'),
            ),
            $denormalized,
            "Denormalization with undefined attributes"
        );
    }

    /**
     * @var EngineBlock_Test_Attributes_MessageRecorder
     */
    protected $_messageRecorder;

    protected function _normalize(array $input, array $definition)
    {
        $this->_messageRecorder = new EngineBlock_Test_Attributes_MessageRecorder();

        $normalizer = new EngineBlock_Attributes_Normalizer($input);
        $normalizer->setLogger($this->_messageRecorder);
        $normalizer->setDefinition($definition);
        return $normalizer->normalize();
    }

    protected function _denormalize(array $input, array $definition)
    {
        $this->_messageRecorder = new EngineBlock_Test_Attributes_MessageRecorder();

        $normalizer = new EngineBlock_Attributes_Normalizer($input);
        $normalizer->setLogger($this->_messageRecorder);
        $normalizer->setDefinition($definition);
        return $normalizer->denormalize();
    }
}
