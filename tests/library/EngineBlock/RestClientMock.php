<?php

class RestClientMock
{
	protected $_method = NULL;
	protected $_params = array();
	protected $_methods = array("metadata", "idplist", "findidentifiersbymetadata", "isconnectionallowed", "arp");
	
	public function __construct()
	{
		$this->_params = array("keys"=>NULL, "key"=>NULL, "value"=>NULL, "entityid"=>NULL, "spentityid"=>NULL, "idpentityid"=>NULL);
	}
	
	public function __call($call, $args)
	{
		if (array_key_exists($call, $this->_params)) {
			$this->_params[$call] = $args[0];
        } else if (in_array($call, $this->_methods)) {
		    $this->_method = $call;
		}
		// fluent interface.
		return $this;
	}
	
	public function get()
	{
        // Mock a number of test scenarios
        if ($this->_method=="metadata" && $this->_params["keys"]==NULL) {
            return array("name:en"=>"Ivo's SP", "description:en"=>"A description", "certData"=>"aaaaabbbbb");
        }
        if ($this->_method=="metadata" && $this->_params["keys"]!=NULL && strpos($this->_params["keys"], ",")!==false) {
            return array("name:en"=>"Ivo's SP", "description:en"=>"A description");
	    }
        if ($this->_method=="metadata" && $this->_params["keys"]!=NULL) {
            return array("certData"=>"aaaaabbbbb");
        }
        if ($this->_method=="isconnectionallowed") {
        	if ($this->_params["spentityid"]=="http://ivotestsp.local" && $this->_params["idpentityid"]=="http://ivoidp") {
        		return array("allowed"=>"yes");
        	}
        	return array("allowed"=>"no");
        }         
        if ($this->_method=="arp") {
            return array("name"=>"someArp", "description"=>"This is a test arp", "attributes"=>array("sn", "url:en", "name:en", "description:en"));	
        }
        if ($this->_method=="findidentifiersbymetadata") {
        	return array("http://ivotestsp.local");
        }
        return NULL;
   	}
}