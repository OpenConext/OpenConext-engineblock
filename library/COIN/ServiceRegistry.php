<?php

require "COIN/Rest/Client.php";
 
class COIN_ServiceRegistry 
{
	protected $_restClient = NULL;
	
    public function getMetadata($entityId) 
    {
    	    	
    	$response = $this->getRestClient()->metadata()
    	                                  ->EntityID($entityId)
    	                                  ->get();
        return $response; 	
    }
    
    public function getMetaDataForKey($entityId, $key)
    {
        $response = $this->getRestClient()->metadata()
                                          ->EntityID($entityId)
                                          ->Keys($key)
                                          ->get();	
        if (isset($response[$key])) {
        	return $response[$key];
        }
        return NULL;
    }
    
    public function getMetaDataForKeys($entityId, $keys)
    {
    	$response = $this->getRestClient()->metadata()
    	                                  ->EntityID($entityId)
    	                                  ->Keys(implode(",", $keys))
    	                                  ->get();
    	return $response;
    }    
    
    public function isConnectionAllowed($spEntityId, $idpEntityId)
    {
    	$response = $this->getRestClient()->isconnectionallowed()
    	                                  ->SPEntityID($spEntityId)
    	                                  ->IDPEntityID($idpEntityId)
    	                                  ->get();
    	return (isset($response["allowed"]) && $response["allowed"]=="yes");
    }
    
    protected function getRestClient()
    {
    	if ($this->_restClient==NULL) {
    		$this->_restClient = new COIN_Rest_Client(ENGINEBLOCK_SERVICEREGISTRY_API_URL);	
    	}
    	return $this->_restClient;
    }
    
    /**
     * Method for injecting a rest client other than the default COIN_Rest_Client, which is useful for
     * unit tests. Should not generally be used in other situations as getRestClient() will construct
     * a default COIN_Rest_Client if necessary.
     * @param Zend_Rest_Client $client The class will work as long as the $client has Zend_Rest_Client's
     *                                 interface, even though the default is a COIN_Rest_Client. 
     */
    public function setRestClient($client) 
    {
    	$this->_restClient = $client;
    	
    }
    
}
