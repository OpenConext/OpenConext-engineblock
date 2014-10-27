<?php

// TODO: replace the calls with a mock rest server, currently tests against
// actual content in Ivo's janus db.

/**
 * ServiceRegistry test case.
 */
class EngineBlock_Test_ServiceRegistryTest extends PHPUnit_Framework_TestCase
{
    
    /**
     * @var Janus_Client
     */
    private $ServiceRegistry;
    
    /**
     * Prepares the environment before running a test.
     */
    protected function setUp() 
    {
        parent::setUp ();
        
        // TODO Auto-generated ServiceRegistryTest::setUp()
        

        $this->ServiceRegistry = new Janus_Client(/* parameters */);
        $this->ServiceRegistry->setRestClient(new EngineBlock_Test_RestClientMock());
    }
    
    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown() 
    {
        // TODO Auto-generated ServiceRegistryTest::tearDown()
        

        $this->ServiceRegistry = null;
        
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
     * Tests ServiceRegistry->getMetadata()
     */
    public function testGetMetadata() 
    {
        
       $result = $this->ServiceRegistry->getMetadata("http://ivotestsp.local");
       $this->assertTrue(is_array($result));
       $this->assertEquals("A description", $result["description:en"]);
    }
    
    public function testGetMetaDataForKey() 
    {
        
        $result = $this->ServiceRegistry->getMetaDataForKey("http://ivotestsp.local", "certData");
        $this->assertTrue(is_string($result));    
        $this->assertEquals("aaaaabbbbb", $result);    
    }
    
    public function testGetMetaDataForKeys() 
    {
        $result = $this->ServiceRegistry->getMetaDataForKeys("http://ivotestsp.local", array("name:en", "description:en"));
        $this->assertEquals(2, count($result));
        $this->assertEquals("Ivo's SP", $result["name:en"]);
        $this->assertEquals("A description", $result["description:en"]);
    }
    
    public function testIsConnectionAllowed() 
    {
        $result = $this->ServiceRegistry->isConnectionAllowed("http://ivotestsp.local", "http://doesntexist.local");
        $this->assertFalse($result);
        
        $result = $this->ServiceRegistry->isConnectionAllowed("http://ivotestsp.local", "http://ivoidp");
        $this->assertTrue($result);
    }
    
    public function testGetArp()
    {
        $result = $this->ServiceRegistry->getArp("http://ivotestsp.local");
        $this->assertEquals(3, count($result));
        $this->assertEquals("someArp", $result["name"]);
        $this->assertEquals(4, count($result["attributes"]));
        $this->assertEquals("sn", $result["attributes"][0]);
    
    }
    
    public function testFindIdentifiersByMetadata()
    {
        $result = $this->ServiceRegistry->findIdentifiersByMetadata("url:en", "www.google.com");
        $this->assertEquals(1, count($result));
        $this->assertEquals("http://ivotestsp.local", $result[0]);
      
    }
    
    public function testGetIdpList()
    {
        $result = $this->ServiceRegistry->getIdpList();
        
        $this->assertEquals(2, count($result));
        $this->assertEquals("Ivo's IDP", $result["http://ivotestidp.local"]["name:en"]);
        
        $result = $this->ServiceRegistry->getIdpList(array(), "someSP");
        $this->assertEquals(1, count($result), "Idplist not correctly filtered by SP");

        $result = $this->ServiceRegistry->getIdpList(array(), NULL);
        $this->assertEquals(2, count($result), "Idplist not correctly ignoring NULL sp");
        
        
    }
    
    public function testGetSpList()
    {
        $result = $this->ServiceRegistry->getSpList();
        $this->assertEquals(2, count($result));
        $this->assertEquals("Ivo's SP", $result["http://ivotestsp.local"]["name:en"]);
    }
}

