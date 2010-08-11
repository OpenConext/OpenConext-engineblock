<?php


require_once 'PHPUnit/Framework/TestCase.php';

require_once 'engineblock/configs/config.inc.php';

require_once 'RestClientMock.php';

// TODO: replace the calls with a mock rest server, currently tests against
// actual content in Ivo's janus db.

/**
 * EngineBlock_ServiceRegistry test case.
 */
class EngineBlock_ServiceRegistryTest extends PHPUnit_Framework_TestCase 
{
	
	/**
	 * @var EngineBlock_ServiceRegistry
	 */
	private $EngineBlock_ServiceRegistry;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() 
	{
		parent::setUp ();
		
		// TODO Auto-generated ServiceRegistryTest::setUp()
		

		$this->EngineBlock_ServiceRegistry = new EngineBlock_ServiceRegistry(/* parameters */);
	    $this->EngineBlock_ServiceRegistry->setRestClient(new RestClientMock());
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() 
	{
		// TODO Auto-generated ServiceRegistryTest::tearDown()
		

		$this->EngineBlock_ServiceRegistry = null;
		
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
	 * Tests EngineBlock_ServiceRegistry->getMetadata()
	 */
	public function testGetMetadata() 
	{
		
	   $result = $this->EngineBlock_ServiceRegistry->getMetadata("http://ivotestsp.local");
	   $this->assertTrue(is_array($result));
	   $this->assertEquals("A description", $result["description:en"]);
	}
	
	public function testGetMetaDataForKey() 
	{
		
		$result = $this->EngineBlock_ServiceRegistry->getMetaDataForKey("http://ivotestsp.local", "certData");
		$this->assertTrue(is_string($result));	
		$this->assertEquals("aaaaabbbbb", $result);	
	}
	
	public function testGetMetaDataForKeys() 
	{
		$result = $this->EngineBlock_ServiceRegistry->getMetaDataForKeys("http://ivotestsp.local", array("name:en", "description:en"));
		$this->assertEquals(2, count($result));
		$this->assertEquals("Ivo's SP", $result["name:en"]);
		$this->assertEquals("A description", $result["description:en"]);
	}
	
	public function testIsConnectionAllowed() 
	{
		$result = $this->EngineBlock_ServiceRegistry->isConnectionAllowed("http://ivotestsp.local", "http://doesntexist.local");
		$this->assertFalse($result);
		
		$result = $this->EngineBlock_ServiceRegistry->isConnectionAllowed("http://ivotestsp.local", "http://ivoidp");
		$this->assertTrue($result);
	}
	
	public function testGetArp()
	{
		$result = $this->EngineBlock_ServiceRegistry->getArp("http://ivotestsp.local");
        $this->assertEquals(3, count($result));
        $this->assertEquals("someArp", $result["name"]);
        $this->assertEquals(4, count($result["attributes"]));
        $this->assertEquals("sn", $result["attributes"][0]);
    
	}
	
	public function testFindIdentifiersByMetadata()
	{
		$result = $this->EngineBlock_ServiceRegistry->findIdentifiersByMetadata("url:en", "www.google.com");
        $this->assertEquals(1, count($result));
        $this->assertEquals("http://ivotestsp.local", $result[0]);
      
	}

}

