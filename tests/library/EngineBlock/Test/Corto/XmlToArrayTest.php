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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Incomplete test class EngineBlock_Test_for xml conversion
 *
 * @todo test also array2attributes
 * @todo test also formatXml *
 */
class EngineBlock_Test_Corto_Module_XMlToArrayTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testNamespacedAttributes()
    {
        $hash = EngineBlock_Corto_XmlToArray::xml2array(
<<<SAML
<?xml version="1.0"?>
<saml:Attribute xmlns:saml="urn:oasis:names:tc:SAML:1.0:assertion"
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <saml:AttributeValue xsi:type="string">test</saml:AttributeValue>
</saml:Attribute>
SAML

        );

        $expected = array (
            '__t' => 'saml:Attribute',
            'saml:AttributeValue' =>
                array (
                    0 =>
                        array (
                            '_xsi:type' => 'string',
                            '__v' => 'test',
                        ),
                ),
        );

        $this->assertEquals($expected, $hash, "Namespaced attributes (like xsi:type) are properly decoded.");
    }

    public function testRegisterNamespaces()
    {
        // <saml:AttributeStatement><saml:Attribute Name="urn:mace:dir:attribute-def:uid" NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"><saml:AttributeValue xsi:type="xs:string">avykq</saml:AttributeValue></saml:Attribute>
        $hash = array(
            EngineBlock_Corto_XmlToArray::TAG_NAME_PFX => 'samlp:Response',
            'saml:Issuer' => 'http://example.edu',
            'saml:Assertion' => array(
                'saml:AttributeStatement' => array(
                    'saml:Attribute' => array(
                        0 => array(
                            '_Name' => 'name',
                            '_NameFormat' => 'a',
                            '_xsi:type' => 'string',
                            'saml:AttributeValue' => array(
                                0 => array(
                                    '__v' => 'GIANTDAD IS BACK',
                                )
                            )
                        ),
                    )
                ),
            ),
        );

        $expected =array (
            '_xmlns:saml' => 'urn:oasis:names:tc:SAML:2.0:assertion',
            '_xmlns:samlp' => 'urn:oasis:names:tc:SAML:2.0:protocol',
            '_xmlns:xsi'   => 'http://www.w3.org/2001/XMLSchema-instance',
            '__t' => 'samlp:Response',
            'saml:Issuer' => 'http://example.edu',
            'saml:Assertion' =>
                array (
                    'saml:AttributeStatement' =>
                        array (
                            'saml:Attribute' =>
                                array (
                                    0 =>
                                        array (
                                            '_Name' => 'name',
                                            '_NameFormat' => 'a',
                                            '_xsi:type' => 'string',
                                            'saml:AttributeValue' =>
                                                array (
                                                    0 =>
                                                        array (
                                                            '__v' => 'GIANTDAD IS BACK',
                                                        ),
                                                ),
                                        ),
                                ),
                        ),
                ),
        );

        $this->assertEquals($expected, EngineBlock_Corto_XmlToArray::registerNamespaces($hash));
    }
    public function testAttributesToArray()
    {
        $attributes = array(
            array(
                '_Name' => 'urn:org:openconext:corto:internal:sp-entity-id',
                'saml:AttributeValue' => array(
                    array(
                        '__v' => 'testSp'
                    )
                )
            ),
            array(
                '_Name' => 'urn:mace:dir:attribute-def:cn',
                'saml:AttributeValue' => array(
                    array(
                        '__v' => 'Joe Smooth'
                    )
                )
            )
        );

        $expectedOutput = array(
            'urn:org:openconext:corto:internal:sp-entity-id' => array('testSp'),
            'urn:mace:dir:attribute-def:cn' => array('Joe Smooth'),
        );

        $xmlConverter = new EngineBlock_Corto_XmlToArray();
        $this->assertEquals($expectedOutput, $xmlConverter->attributesToArray($attributes));
    }

    public function testAttributeNameIsRequired()
    {
        $this->expectException(EngineBlock_Corto_XmlToArray_Exception::class);
        $this->expectExceptionMessage('Missing attribute name');

        $xmlConverter = new EngineBlock_Corto_XmlToArray();
        $attributes = array(array());
        $xmlConverter->attributesToArray($attributes);
    }

    /**
     */
    public function testAttributeEmptyValueCollectionIsSkipped()
    {
        $xmlConverter = new EngineBlock_Corto_XmlToArray();
        $attributes = array(
            array(
                '_Name' => 'example'
            )
        );
        $output = $xmlConverter->attributesToArray($attributes);

        $this->assertEquals(array(), $output['example']);
    }

    public function testAttributeValueCollectionShouldBeAnArray()
    {
        $this->expectException(EngineBlock_Corto_XmlToArray_Exception::class);
        $this->expectExceptionMessage('AttributeValue collection is not an array');

        $xmlConverter = new EngineBlock_Corto_XmlToArray();
        $attributes = array(
            array(
                '_Name' => 'example',
                'saml:AttributeValue' => ''
            )
        );
        $xmlConverter->attributesToArray($attributes);
    }

    public function testAttributeValueShouldBeAnArray()
    {
        $this->expectException(EngineBlock_Corto_XmlToArray_Exception::class);
        $this->expectExceptionMessage('AttributeValue is not an array');

        $xmlConverter = new EngineBlock_Corto_XmlToArray();
        $attributes = array(
            array(
                '_Name' => 'example',
                'saml:AttributeValue' => array(
                    null
                )
            )
        );
        $xmlConverter->attributesToArray($attributes);
    }

    public function testAttributeValueIsSkippedWhenEmpty()
    {
        $xmlConverter = new EngineBlock_Corto_XmlToArray();
        $attributes = array(
            array(
                '_Name' => 'example',
                'saml:AttributeValue' => array(
                    array(
                        '__v' => null
                    )
                )
            )
        );

        $expectedArray = array(
            'example' => array()
        );
        $this->assertEquals($expectedArray, $xmlConverter->attributesToArray($attributes));
    }

    /**
     * @dataProvider xmlInputProvider
     */
    public function testXmlToArray($xmlFile, $phpFile)
    {
        $xmlInput = file_get_contents($xmlFile);
        $expectedPhpOutput = require $phpFile;

        $this->assertEquals($expectedPhpOutput, EngineBlock_Corto_XmlToArray::xml2array($xmlInput));
    }

    /**
     * @dataProvider xmlOutputProvider
     */
    public function testArrayToXml($phpFile, $xmlFile)
    {
        $phpInput = require $phpFile;
        $expectedXmlOutput = file_get_contents($xmlFile);

        $this->assertEquals($expectedXmlOutput, EngineBlock_Corto_XmlToArray::array2xml($phpInput));
    }

    /**
     * Loads a set of xml to php testcases from resources dir
     *
     * @return array
     */
    public function xmlInputProvider()
    {
        $testCasesDir = new DirectoryIterator(TEST_RESOURCES_DIR . '/xml-to-array');

        $testCases = array();
        /** @var $testCaseDir DirectoryIterator */
        foreach($testCasesDir as $testCaseDir) {
            if ($testCaseDir->isDot()) {
                continue;
            }

            $testCases[$testCaseDir->getFilename()] = array(
                'xmlFile' => $testCaseDir->getPathname() . '/input.xml',
                'phpFile' => $testCaseDir->getPathname() . '/output.php'
            );
        }

        return $testCases;
    }

    /**
     * Loads a set of php to xml testcases from resources dir
     *
     * @return array
     */
    public function xmlOutputProvider()
    {
        $testCasesDir = new DirectoryIterator(TEST_RESOURCES_DIR . '/xml-to-array');

        $testCases = array();
        /** @var $testCaseDir DirectoryIterator */
        foreach($testCasesDir as $testCaseDir) {
            if ($testCaseDir->isDot()) {
                continue;
            }

            $testCases[$testCaseDir->getFilename()] = array(
                'phpFile' => $testCaseDir->getPathname() . '/output.php',
                'xmlFile' => $testCaseDir->getPathname() . '/output.xml'
            );
        }

        return $testCases;
    }
}
