<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

require_once(dirname(__FILE__) . '/../../../autoloading.inc.php');
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'FileMock.php';

class EngineBlock_AttributeManipulator_FileTest extends PHPUnit_Framework_TestCase
{
    public function tesGetDirectoryForEntityId()
    {
        $result = EngineBlock_AttributesManipulator_FileMock::_getDirectoryNameForEntityId(
            'https://s1304.pixsoftware.de'
        );
        $this->assertEquals('https___s1304.pixsoftware.de', $result, 'Allowed: numbers and dots');

        $result = EngineBlock_AttributesManipulator_FileMock::_getDirectoryNameForEntityId(
            'https://'
        );
        $this->assertEquals('https___', $result, "Convert https:// to https___");

        $result = EngineBlock_AttributesManipulator_FileMock::_getDirectoryNameForEntityId('SURFnet%20BV');
        $this->assertEquals('SURFnet_20BV', $result, "Convert % (from a URL encoding) to an _");
    }

    public function testManipulation()
    {
        EngineBlock_AttributesManipulator_FileMock::setMockFileLocation(dirname(__FILE__) . '/fixtures/attribute-manipulations/');
        $fileManipulator = new EngineBlock_AttributesManipulator_FileMock();

        $subjectId = 'urn:collab:person:example.com:testuser';
        $response = array('__'=>array('destinationid'=>'https://example.com'));
        $attributes = array('test'=>'1');

        $fileManipulator->manipulate(
            $response['__']['destinationid'],
            $subjectId,
            $attributes,
            $response
        );
        $this->assertEquals(
            array('test'=>'1', 'example.com'=>'test'),
            $attributes,
            'Test basic manipulation'
        );

        $response['__'] = array('destinationid'=>"https://example.com/test/response");
        $attributes = array_merge($attributes, array('urn:mace:dir:attribute-def:mail'=>'test@example.com'));
        $fileManipulator->manipulate(
            $response['__']['destinationid'],
            $subjectId,
            $attributes,
            $response
        );
        $this->assertEquals(
            array(
                'test' => '1',
                'email'=>'test@example.com',
                'example.com' => 'test',
                'uid'=>$subjectId,
                'sp'=>$response['__']['destinationid'],
            ),
            $attributes,
            "Testing attribute renaming and using the subjectId and response"
        );
    }
}
