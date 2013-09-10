<?php
/**
 * Incomplete test class EngineBlock_Test_for xml conversion
 *
 * @todo test also array2attributes
 * @todo test also formatXml *
 */
class EngineBlock_Test_Corto_Module_XMlToArrayTest extends PHPUnit_Framework_TestCase
{
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

    /**
     * @expectedException EngineBlock_Corto_XmlToArray_Exception
     * @expectedExceptionMessage Missing attribute name
     */
    public function testAttributeNameIsRequired()
    {
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

    /**
     * @expectedException EngineBlock_Corto_XmlToArray_Exception
     * @expectedExceptionMessage AttributeValue collection is not an array
     */
    public function testAttributeValueCollectionShouldBeAnArray()
    {
        $xmlConverter = new EngineBlock_Corto_XmlToArray();
        $attributes = array(
            array(
                '_Name' => 'example',
                'saml:AttributeValue' => ''
            )
        );
        $xmlConverter->attributesToArray($attributes);
    }

    /**
     * @expectedException EngineBlock_Corto_XmlToArray_Exception
     * @expectedExceptionMessage AttributeValue is not an array
     */
    public function testAttributeValueShouldBeAnArray()
    {
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

    /**
     */
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
