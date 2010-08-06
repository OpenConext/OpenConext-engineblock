<?php

require_once 'COIN/Provisioning.php';

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * COIN_Provisioning test case.
 */
class COIN_ProvisioningTest extends PHPUnit_Framework_TestCase 
{
	
	/**
	 * @var COIN_Provisioning
	 */
	private $COIN_Provisioning;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() 
	{
		parent::setUp ();
		
		// TODO Auto-generated COIN_ProvisioningTest::setUp()
		

		$this->COIN_Provisioning = new COIN_Provisioning(/* parameters */);
	
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() 
	{
		// TODO Auto-generated COIN_ProvisioningTest::tearDown()
		

		$this->COIN_Provisioning = null;
		
		parent::tearDown ();
	}
	
	/**
	 * Constructs the test case.
	 */
	public function __construct() 
	{
		// TODO Auto-generated constructor
	}

}

