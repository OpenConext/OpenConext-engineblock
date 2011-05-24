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

require_once(dirname(__FILE__) . '/../../autoloading.inc.php');
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * EngineBlock_Provisioning test case.
 */
class EngineBlock_ProvisioningTest extends PHPUnit_Framework_TestCase 
{
	
	/**
	 * @var EngineBlock_Provisioning
	 */
	private $EngineBlock_Provisioning;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() 
	{
		parent::setUp ();
		
		// TODO Auto-generated COIN_ProvisioningTest::setUp()
		

		$this->EngineBlock_Provisioning = new EngineBlock_Provisioning(/* parameters */);
	
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() 
	{
		// TODO Auto-generated EngineBlock_ProvisioningTest::tearDown()
		

		$this->EngineBlock_Provisioning = null;
		
		parent::tearDown ();
	}
	
	/**
	 * Constructs the test case.
	 */
	public function __construct() 
	{
		// TODO Auto-generated constructor
	}

    public function testProvisionUser() {
        $this->markTestIncomplete("testProvisionUser not yet implemented");
//        $attributes = array('one' => array('one-value'), 'two' => array('first-value', 'second-value'));
//        $hash = '1234567890';
//        $result = $this->EngineBlock_Provisioning->provisionUser($attributes, $hash);
//        $this->assertTrue($result);
    }
}
