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

/**
 * EngineBlock_SocialData_FieldMapper test case.
 */
class EngineBlock_Test_SocialData_FieldMapperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EngineBlock_SocialData_FieldMapper
     */
    private $EngineBlock_SocialData_FieldMapper;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        // TODO Auto-generated EngineBlock_SocialData_FieldMapperTest::setUp()
        $this->EngineBlock_SocialData_FieldMapper = new EngineBlock_SocialData_FieldMapper(/* parameters */);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated EngineBlock_SocialData_FieldMapperTest::tearDown()
        $this->EngineBlock_SocialData_FieldMapper = null;
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {    // TODO Auto-generated constructor
    }

    /**
     * Tests EngineBlock_SocialData_FieldMapper->socialToLdapAttributes()
     */
    public function testSocialToLdapAttributes()
    {
       // check regular conversion
       $socialAttributes = array("id", "nickname");
       $ldapAttributes = $this->EngineBlock_SocialData_FieldMapper->socialToLdapAttributes($socialAttributes);
       $this->assertEquals(array("collabpersonid", "displayname"), $ldapAttributes);

       // check that if 2 names lead to the same ldap attr, it's present in the output only once.
       $socialAttributes = array("id", "nickname", "displayName");
       $ldapAttributes = $this->EngineBlock_SocialData_FieldMapper->socialToLdapAttributes($socialAttributes);
       $this->assertEquals(array("collabpersonid", "displayname"), $ldapAttributes);
       
       // check that if something that doesn't exist is passed, it's untouched in the output
       $socialAttributes = array("nickname", "somethingthatdoesntexist");
       $ldapAttributes = $this->EngineBlock_SocialData_FieldMapper->socialToLdapAttributes($socialAttributes);
       $this->assertEquals(array("displayname", "somethingthatdoesntexist"), $ldapAttributes);
       
       
    }

    /**
     * Tests EngineBlock_SocialData_FieldMapper->ldapToSocialData()
     */
    public function testLdapToSocialData()
    {
       // Check conversion of a record, without filtering.
       $ldapRecord = array("collabpersonid"=>"urn:collab:surfnet.nl:hansz",
                           "displayname"=>"Hans Zandbelt");
       $socialRecord = $this->EngineBlock_SocialData_FieldMapper->ldapToSocialData($ldapRecord);
       $this->assertEquals(array("id"=>"urn:collab:surfnet.nl:hansz",
                                 "displayName"=>"Hans Zandbelt",
                                 "nickname"=>"Hans Zandbelt"), $socialRecord);
       
       // Check converstion, filtering to only a single field
       // Check conversion of a record, without filtering.
       $ldapRecord = array("collabpersonid"=>"urn:collab:surfnet.nl:hansz",
                           "displayname"=>"Hans Zandbelt");
       $socialRecord = $this->EngineBlock_SocialData_FieldMapper->ldapToSocialData($ldapRecord, array("nickname"));
       $this->assertEquals(array("nickname"=>"Hans Zandbelt"), $socialRecord);
       
       // Check converstion, check conversion of single to multivalues
       // Check conversion of a record, without filtering.
       $ldapRecord = array("collabpersonid"=>"urn:collab:surfnet.nl:hansz",
                           "displayname"=>array("Hans Zandbelt"),
                           "mail"=>"test@test.com");
       $socialRecord = $this->EngineBlock_SocialData_FieldMapper->ldapToSocialData($ldapRecord, 
                                                                        array("displayName", "emails"));
       $this->assertEquals(array("displayName"=>"Hans Zandbelt", "emails"=>array("test@test.com")), $socialRecord);
       
       // Check if we cleanly handle nonsense keys (return empty array)
       $ldapRecord = array("collabpersonid"=>"urn:collab:surfnet.nl:hansz");
       $socialRecord = $this->EngineBlock_SocialData_FieldMapper->ldapToSocialData($ldapRecord, array("batcaveVolume"));
       $this->assertEquals(count($socialRecord), 0);
       
    }
}

