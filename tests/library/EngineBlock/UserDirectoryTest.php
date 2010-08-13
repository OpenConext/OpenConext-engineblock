<?php

require_once dirname(__FILE__) . '/../../autoloading.inc.php';
require_once 'PHPUnit/Framework/TestCase.php';

// @todo replace Zend_Ldap with a mock object so we can test without hitting the actual ldap server

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
		
        $application = EngineBlock_ApplicationSingleton::getInstance();
        $config = array();
        require ENGINEBLOCK_FOLDER_APPLICATION . 'configs/application.php';
        $application->setConfiguration($config);
		
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

    public function testfindUsersByIdentifier() {
        $result = $this->EngineBlock_UserDirectory->findUsersByIdentifier('urn:collab:person:surfnet.nl:hansz');
        $this->assertEquals(1, count($result));
        $this->assertEquals('uid=hansz,o=surfnet.nl,dc=coin,dc=surfnet,dc=nl', $result[0]["dn"]);
        
        $result = $this->EngineBlock_UserDirectory->findUsersByIdentifier('urn:collab:person:surfnet.nl:hansz', array('givenname'));
        $this->assertEquals(1, count($result));
        $this->assertEquals(2, count($result[0])); // contains dn and the requested attribuets
        $this->assertEquals('Hans', $result[0]['givenname'][0]); // cound be multiple so is an array
        
        $result = $this->EngineBlock_UserDirectory->findUsersByIdentifier('batman:or:another:name:that:cant:possibly:exist');
        $this->assertType("array", $result);
        $this->assertEquals(0, count($result));
    }
    
    public function testAddUser() {
        $result = $this->EngineBlock_UserDirectory->addUser('test.nl', array('urn:mace:dir:attribute-def:uid' => array('pietje')), '1234567890');
        $this->assertTrue($result);
    }    
}
