<?php

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * EngineBlock_UserDirectory test case.
 */
class EngineBlock_UserDirectoryTest extends PHPUnit_Framework_TestCase 
{
	
	/**
	 * @var EngineBlock_UserDirectory
	 */
	private $EngineBlock_UserDirectory;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() 
	{
		parent::setUp ();
		
		// TODO Auto-generated EngineBlock_UserDirectoryTest::setUp()
		

		$this->EngineBlock_UserDirectory = new EngineBlock_UserDirectory(/* parameters */);
	
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() 
	{
		// TODO Auto-generated EngineBlock_UserDirectoryTest::tearDown()
		

		$this->EngineBlock_UserDirectory = null;
		
		parent::tearDown ();
	}
	
	/**
	 * Constructs the test case.
	 */
	public function __construct() 
	{
		// TODO Auto-generated constructor
	}
	
	/**
	 * Tests EngineBlock_UserDirectory->registerUserForAttributes()
	 */
	public function testRegisterUserForAttributes() 
	{
		// TODO Auto-generated EngineBlock_UserDirectoryTest->testRegisterUserForAttributes()
		$this->markTestIncomplete ( "registerUserForAttributes test not implemented" );
		
		$this->EngineBlock_UserDirectory->registerUserForAttributes(/* parameters */);
	
	}

}

