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

require_once 'EngineBlock/Groups/Grouper.php';
require_once 'PHPUnit/Framework/TestCase.php';

require_once dirname(__FILE__) . '/../../../autoloading.inc.php';


/**
 * EngineBlock_Groups_Grouper test case.
 */
class EngineBlock_Groups_GrouperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EngineBlock_Groups_Grouper
     */
    private $EngineBlock_Groups_Grouper;

    /**
     * Prepares the environment before running a test.
     */
//    protected function setUp()
//    {
//        parent::setUp();
//
//        $application = EngineBlock_ApplicationSingleton::getInstance();
//        $config = array();
//        require ENGINEBLOCK_FOLDER_APPLICATION . 'configs/application.php';
//        $application->setConfiguration($config['ivodev']);
//
//
//        // TODO Auto-generated EngineBlock_Groups_GrouperTest::setUp()
//        $this->EngineBlock_Groups_Grouper = new EngineBlock_Groups_Grouper(/* parameters */);
//    }
//
//    /**
//     * Cleans up the environment after running a test.
//     */
//    protected function tearDown()
//    {
//        // TODO Auto-generated EngineBlock_Groups_GrouperTest::tearDown()
//        $this->EngineBlock_Groups_Grouper = null;
//        parent::tearDown();
//    }
//
//    /**
//     * Constructs the test case.
//     */
//    public function __construct()
//    {    // TODO Auto-generated constructor
//    }
//
    /**
     * Tests EngineBlock_Groups_Grouper->getGroups()
     */
    public function testGetGroups()
    {
        $this->markTestIncomplete ( "testGetGroups test not implemented" );
        
        //$groups = $this->EngineBlock_Groups_Grouper->getGroups("urn:collab:person:fontys.nl:874501@fontys.nl");
        
        //var_dump($groups);
    }
}

