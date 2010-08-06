<?php

require_once 'engineblock/library/COIN/UserDirectory.php';

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * COIN_UserDirectory test case.
 */
class COIN_UserDirectoryTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @var COIN_UserDirectory
	 */
	private $COIN_UserDirectory;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
		
		// TODO Auto-generated COIN_UserDirectoryTest::setUp()
		

		$this->COIN_UserDirectory = new COIN_UserDirectory(/* parameters */);
	
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated COIN_UserDirectoryTest::tearDown()
		

		$this->COIN_UserDirectory = null;
		
		parent::tearDown ();
	}
	
	/**
	 * Constructs the test case.
	 */
	public function __construct() {
		// TODO Auto-generated constructor
	}
	
	/**
	 * Tests COIN_UserDirectory->registerUserForAttributes()
	 */
	public function testRegisterUserForAttributes() {
		// TODO Auto-generated COIN_UserDirectoryTest->testRegisterUserForAttributes()
		$this->markTestIncomplete ( "registerUserForAttributes test not implemented" );
		
		$this->COIN_UserDirectory->registerUserForAttributes(/* parameters */);
	
	}

}

