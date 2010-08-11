<?php

require_once("Zend/Rest/Client.php");
   
/**
 * This extends Zend_Rest_Client with an improved way of retrieving
 * results.
 *  
 * Zend_Rest_Client uses Zend_Rest_Response which only handles 
 * XML requests. EngineBlock_Rest_Client first checks the Content-Type
 * header of the result. If it's application/json we simply
 * json_decode the result, if it's anything else, the original
 * Zend_Rest_Client behaviour is used (which is to invoke an xml
 * parser).
 * 
 * Note: the issue that Zend_Rest_Client is json unfriendly has been
 * logged as:
 * http://framework.zend.com/issues/browse/ZF-10272
 * 
 * Keep an eye on this ticket; if it gets fixed, this override
 * may no longer be necessary.
 * 
 * @author ivo
 *
 */
class EngineBlock_Rest_Client extends Zend_Rest_Client
{
    public function __call($method, $args)
    {
        if ($method=='get') {
        	
            if (!isset($args[0])) {
                $args[0] = $this->_uri->getPath();
            }
            $this->_data['rest'] = 1;
            $data = array_slice($args, 1) + $this->_data;
            $response = $this->{'rest' . $method}($args[0], $data);
            $this->_data = array();//Initializes for next Rest method.
            
            // The next line has been changed to not use Zend_Rest_Client_Result for json responses
            if ($response->getHeader("Content-Type")=="application/json") {
                return json_decode($response->getBody(), true);
            } else {
            	return new Zend_Rest_Client_Result($response->getBody());
            }
        } else {
        	// In all other situations we're happy with the default behaviour.
        	return parent::__call($method, $args);
        }
    }
   	
}