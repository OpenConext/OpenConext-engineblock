<?php

require_once(dirname(__FILE__) . '/../../../autoloading.inc.php');
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'FileMock.php';

class EngineBlock_AttributeManipulator_FileTest extends PHPUnit_Framework_TestCase
{
    public function test_getDirectoryForEntityId()
    {
        $result = EngineBlock_AttributeManipulator_FileMock::_getDirectoryNameForEntityId(
            'https://s1304.pixsoftware.de'
        );
        $this->assertEquals('https___s1304.pixsoftware.de', $result, 'Allowed: numbers and dots');

        $result = EngineBlock_AttributeManipulator_FileMock::_getDirectoryNameForEntityId(
            'https://'
        );
        $this->assertEquals('https___', $result, "Convert https:// to https___");

        $result = EngineBlock_AttributeManipulator_FileMock::_getDirectoryNameForEntityId('SURFnet%20BV');
        $this->assertEquals('SURFnet_20BV', $result, "Convert % (from a URL encoding) to an _");
    }

    public function test_manipulation()
    {
        $fileManipulator = new EngineBlock_AttributeManipulator_FileMock();
        $fileManipulator->_setFileLocation('./fixtures/attribute-manipulations/');

        $subjectId = 'urn:collab:person:example.com:testuser';
        $response = array('_Destination'=>'https://example.com');
        $defaultAttributes = array('test'=>'1');

        $attributes = $fileManipulator->manipulate(
            $subjectId,
            $defaultAttributes,
            $response
        );
        $this->assertEquals(
            array('test'=>'1', 'example.com'=>'test', 'general'=>'test'),
            $attributes,
            'Test basic manipulation'
        );

        $response['_Destination'] = "https://example.com/test/response";
        $attributes = $fileManipulator->manipulate(
            $subjectId,
            array_merge($defaultAttributes, array('urn:mace:dir:attribute-def:mail'=>'test@example.com')),
            $response
        );
        $this->assertEquals(
            array(
                 'test' => '1',
                 'email'=>'test@example.com',
                 'uid'=>$subjectId, 
                 'sp'=>$response['_Destination'],
                 'general'=>'test',
            ),
            $attributes,
            "Testing attribute renaming and using the subjectId and response"
        );
    }
}
