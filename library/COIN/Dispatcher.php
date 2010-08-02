<?php

// header

/**
 * Main dispatch class
 * 
 * Responsible for intercepting http requests on Engine Block and routing them to the proper
 * underlying modules.
 * 
 * @author ivo
 *
 */
class COIN_Dispatcher
{
	/**
	 * Execute a COIN request based on an input URI.
	 * @param String $requestUri The URI to process, typically this is
	 *                           $_SERVER['REQUEST_URI'].
	 * @param Array $request The request parameters to pass on to the 
	 *                       underlying modules. Typically $_REQUEST
	 *                       should be passed.
	 */
    public function dispatch($requestUri, $request)
    {
    	// Very crude url -> module mapping
    	$module = $this->shiftUri($requestUri);
    	
    	switch ($module) {
    		case "auth":
                $this->_processCortoRequest($requestUri, $request);
                break;
    		case "service":
    			$this->_processServiceRegistryRequest($requestUri, $request);
    			break;
    		case "social":
    			$this->_processOpenSocialRequest($requestUri, $request);
    		case "wayf":
    			$this->_processWayfRequest($requestUri, $request);
    			break;
    		case "consent":
    			$this->processConsentRequest($requestUri, $request);
    			break;
    		default:
    	       $this->output404();
    	}
    }
    
    /**
     * Shift an element off the beginning of a URL. 
     * 
     * This method takes a url such as /service/getservice/x?id=1 and does 
     * the following:
     * - It determines that the first element is 'service' and returns that.
     * - It takes the 'service' part out of the url and changes the url to 
     *   a relative url, in this case 'getservice/x?id=1'
     * @param String $uri The uri to process.
     * @return String The first element of the URI, with any leading/trailing slashes removed.
     * 
     */
    public function shiftUri(&$uri)
    {
    	$qPos = strpos($uri, "?");
        $params = "";
    	if ($qPos!==false) {
    		$params = substr($uri,$qPos);
    		$uri = substr($uri,0,$qPos);
    	}
    	$start = (substr($uri,0,1)=="/")?1:0;
    	$nextSlash = strpos($uri,"/",$start);
    	$return = substr($uri, $start, $nextSlash==false?strlen($uri):$nextSlash-$start);
    	$uri = $nextSlash==false?"":substr($uri, $nextSlash);
    	$uri.=$params; 
    	return $return;
    }
    
    /**
     * Send a request to Corto.
     * 
     * This method simply includes Corto and leaves all other processing up to Corto.
     * @param String $uri The relative request uri 
     * @param Array $request This parameter is present for consistency with the other
     *                       _process*Request methods but is not used by Corto.
     */
    protected function _processCortoRequest($uri, $request)
    {
    	define('CORTO_APPLICATION_OVERRIDES_DIRECTORY', realpath(dirname(__FILE__).'/../').'/');
        require './../corto/www/corto.php';
    }
    
    /**
     * Send a request to the Service Registry.
     * @param String $uri The relative request uri 
     * @param Array $request The request parameters.
     */
    protected function _processServiceRegistryRequest($uri, $request)
    {
    	// @todo talk to COIN_ServiceRegistry and output JSON (or SAML metadata depending on the request)
    	echo "$uri not implemented";
    }

    /**
     * Handle retrieval of Open Social data.
     * @param String $uri The relative request uri 
     * @param Array $request The request parameters.
     */
    protected function _processOpenSocialRequest($uri, $request)
    {
        // @todo talk to COIN_SocialData to retrieve data and output JSON
        echo "$uri not implemented";
    }
    
    
    /**
     * Handle the output/processing of Wayf forms
     * @param String $uri The relative request uri 
     * @param Array $request The request parameters.
     */
    protected function _processWayfRequest($uri, $request)
    {
        // @todo Use COIN_Form_WAYF to display a WAYF form and process results
        echo "$uri not implemented";
    }
    
    /**
     * Handle the output/processing of user consent
     * @param String $uri The relative request uri 
     * @param Array $request The request parameters.
     */
    protected function _processConsentRequest($uri, $request)
    {
        // @todo Use COIN_Form_Consent to display a consent form and process results
        echo "$uri not implemented";
    }
    
    /**
     * Output a 404 header + basic 'Not found' message.
     */
    public function output404()
    {
    	header("HTTP/1.0 404 Not Found");
    	echo "Not found.";
    }
}