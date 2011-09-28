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

require_once dirname(__FILE__) . '/../../autoloading.inc.php';
require_once 'PHPUnit/Framework/TestCase.php';

require_once 'UserDirectoryMock.php';

// @todo replace Zend_Ldap with a mock object so we can test without hitting the actual ldap server

/**
 * EngineBlock_UserDirectory test case.
 */
class EngineBlock_UserDirectoryTest extends PHPUnit_Framework_TestCase 
{
    /**
     * @var EngineBlock_UserDirectoryMock
     */
    protected $_userDirectory;
    
    /**
     * Set the application configuration for LDAP and instantiate a UserDirectory
     */
//    protected function setUp() 
//    {
//        $application = EngineBlock_ApplicationSingleton::getInstance();
//
//        $config = array();
//        require ENGINEBLOCK_FOLDER_APPLICATION . 'configs/application.php';
//        $application->setConfiguration($config['ebdev.net']);
//
//        $this->_userDirectory = new EngineBlock_UserDirectoryMock();
//    }
//
//    /**
//     * Unset the application configuration
//     */
//    protected function tearDown()
//    {
//        EngineBlock_ApplicationSingleton::getInstance()->setConfiguration(array());
//    }
//
    /**
     * Tests EngineBlock_UserDirectory->registerUserForAttributes()
     */
    public function testRegisterUserForAttributes()
    {
        $this->markTestIncomplete ( "registerUserForAttributes test not implemented" );

//        $this->_userDirectory->registerUserForAttributes();
    }
//
//    public function testCommonNameFromAttributes()
//    {
//        $attributes = array(
//            'givenName'     => array('Hans'),
//            'sn'            => array('Zandbelt'),
//            'displayName'   => array('Da Hansz'),
//            'mail'          => array('hans.zandbelt@surfnet.nl'),
//            'uid'           => array('urn:collab:person:surfnet.nl:hansz'),
//            'random'        => array('42'),
//        );
//
//        $cN = $this->_userDirectory->getCommonNameFromAttributes($attributes);
//        $cNExpected = "Hans Zandbelt";
//        $this->assertEquals($cNExpected, $cN, "Given all attributes, prefer the combination of given name and surname");
//
//        unset($attributes['givenName']);
//        $cN = $this->_userDirectory->getCommonNameFromAttributes($attributes);
//        $cNExpected = "Zandbelt";
//        $this->assertEquals($cNExpected, $cN, "With no given name, only a surname, use only the surname as common name.");
//
//        unset($attributes['sn']);
//        $cN = $this->_userDirectory->getCommonNameFromAttributes($attributes);
//        $cNExpected = "Da Hansz";
//        $this->assertEquals($cNExpected, $cN, "With no given name and surname, use the display name as common name.");
//
//        unset($attributes['displayName']);
//        $cN = $this->_userDirectory->getCommonNameFromAttributes($attributes);
//        $cNExpected = "hans.zandbelt@surfnet.nl";
//        $this->assertEquals($cNExpected, $cN, "Given only mail and uid, prefer mail as common name");
//
//        unset($attributes['mail']);
//        $cN = $this->_userDirectory->getCommonNameFromAttributes($attributes);
//        $cNExpected = "urn:collab:person:surfnet.nl:hansz";
//        $this->assertEquals($cNExpected, $cN, "Given only the uid, use the UID");
//
//        unset($attributes['uid']);
//        $cN = $this->_userDirectory->getCommonNameFromAttributes($attributes);
//        $cNExpected = "";
//        $this->assertEquals($cNExpected, $cN, "Given NO identifying attributes, return empty string for common name");
//    }
//
//    public function testFindUsersByIdentifier()
//    {
//        $result = $this->_userDirectory->findUsersByIdentifier('urn:collab:person:surfnet.nl:hansz');
//        var_dump($result);
//        $this->assertEquals(1, count($result));
//        $this->assertEquals('Hans Zandbelt', $result[0]["displayname"][0]);
//
//        $result = $this->_userDirectory->findUsersByIdentifier('urn:collab:person:surfnet.nl:hansz', array('displayname'));
//        $this->assertEquals(1, count($result));
//        $this->assertEquals(2, count($result[0])); // contains just the requested attributes + dn
//        $this->assertEquals('Hans Zandbelt', $result[0]['displayname'][0]);
//
//        $result = $this->_userDirectory->findUsersByIdentifier('batman:or:another:name:that:cant:possibly:exist');
//        $this->assertType("array", $result);
//        $this->assertEquals(0, count($result));
//    }
//
//    public function testAddUser()
//    {
//        $result = $this->_userDirectory->addUser('test.nl', array('urn:mace:dir:attribute-def:uid' => array('pietje')), '1234567890');
//        $this->assertTrue($result);
//    }
}
