<?php

require_once 'COIN/Dispatcher.php';

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * COIN_Dispatcher test case.
 */
class COIN_DispatcherTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @var COIN_Dispatcher
	 */
	private $COIN_Dispatcher;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
		
		// TODO Auto-generated DispatcherTest::setUp()
		

		$this->COIN_Dispatcher = new COIN_Dispatcher(/* parameters */);
	
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated DispatcherTest::tearDown()
		

		$this->COIN_Dispatcher = null;
		
		parent::tearDown ();
	}
	
	
	/**
	 * Tests COIN_Dispatcher->shiftUri()
	 */
    public function testShiftUri() {
		
    	// If we provide the following inputs: 
		$uris = array("test", "test?a=b", "test/x", "test/x/y", "test/x/y/", "/test/x/y/", "/test/x/y?a=b");

		// Then in all cases we expect the following as first element, returned from shiftUri:
		$expected_shift = "test";
		
		// And the relative url left after shifting should then be:
		$expected_relativeuris = array("", "?a=b", "x", "x/y", "x/y/", "x/y/", "x/y?a=b");
		
		for ($i=0;$i<count($uris); $i++) {
		
			$origuri = $uris[$i];
			$expected_relativeuri = $expected_relativeuris[$i];
			
			$uri = $origuri;
            $shift = $this->COIN_Dispatcher->shiftUri($uri);
            
            $this->assertEquals($expected_relativeuri, $uri, "Didn't correctly modify $origuri.");
            $this->assertEquals($expected_shift, $shift, "Didn't correctly retrieve first element from $origuri.");
		
		}
	
	}

}

