<?php
/**
 * Incomplete test class for xml conversion
 *
 * @todo test also xml2array
 * @todo test also array2xml
 * @todo test also array2attributes
 * @todo test also formatXml *
 */
class EngineBlock_Corto_Module_XMlToArrayTest extends PHPUnit_Framework_TestCase
{
    public function testAttributesToArray()
    {
        $input = array(
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
                        '__v' => null
                    )
                )
            )
        );

        $expectedOutput = array(
            'urn:org:openconext:corto:internal:sp-entity-id' => array('testSp'),
            'urn:mace:dir:attribute-def:cn' => array(null),
        );

        $xmlConverter = new EngineBlock_Corto_XmlToArray();
        $this->assertEquals($expectedOutput, $xmlConverter->attributesToArray($input));
    }

    /**
     * @dataProvider xmlDataProvider
     */
    public function testXmlToArray($xmlFile, $phpFile)
    {
        $xmlInput = file_get_contents($xmlFile);
        $expectedPhpOutput = require $phpFile;

        $this->assertEquals($expectedPhpOutput, EngineBlock_Corto_XmlToArray::xml2array($xmlInput));
    }

    /**
     * Loads a set of testcases from resources dir
     *
     * @return array
     */
    public function xmlDataProvider()
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
                'phpFile' => $testCaseDir->getPathname() . '/output.php',
            );
        }

        return $testCases;
    }
}
