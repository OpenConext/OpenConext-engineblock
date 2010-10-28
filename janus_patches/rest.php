<?php

// Place this file in the modules/janus-ssp/www directory.
//
// By default the rest interface is disabled as it currently doesn't feature
// authentication and opens up the registry to outside use. Comment this line
// if you understand the risks and want to enable the REST service.
#header("HTTP/1.1 503 Service Temporarily Unavailable");
#die("REST interface disabled"); 


// for debugging
// error_reporting(E_ALL);
// ini_set('display_errors', true);

/**
 * Small utility class to look up the eid of an Entity based on an entityid
 * or search query; 
 * Janus only works with eid's internally so we need an eid as soon as we 
 * can get one.
 * sspmod_janus_Database can only be used by inheriting from it and I
 * didn't want to use inheritance for my main class, hence this utility class.
 * @author Ivo
 */
class sspmod_janus_EntityFinder extends sspmod_janus_Database
{
   /**
     * Create a new entityFinder
     *
     * @param SimpleSAML_Configuration $config JANUS configuration 
     */
    public function __construct(SimpleSAML_Configuration $config)
    {
        // Send DB config to parent class
        parent::__construct($config->getValue('store'));
        $this->_config = $config;
    }
    
    /**
     * Retrieve the 'Eid' (internal janus db id) for an 'EntityID' (URN).
     * @param String $entityid The URN of the entity to look up
     */
    public function findEid($entityid)   
    {
        $st = $this->execute(
            'SELECT eid 
            FROM '. self::$prefix .'entity 
            WHERE `entityid` = ?;',
            array($entityid)
        );

        if ($st === false) {
            return 'error_db';
        }

        $row = $st->fetchAll(PDO::FETCH_ASSOC);
        return $row[0]['eid'];
    }
    
    /**
     * Retrieve all Eids for entities that match a certain metadata value.
     * 
     * The query is revision aware (only searches the latest revision of every
     * entity)
     * 
     * Note that this function supports regular expressions in the metadata 
     * value. If a metadata entry in the database is a regular expression, 
     * it will be matched against the $value passed to this function. This
     * works only one way, it's not possible to pass a regular expression 
     * to this function; the regex must be in the db.
     * 
     * @param String $key   The metadata key on which to perform the search
     * @param String $value The value to search for. 
     */
    public function findEidsByMetadata($key, $value)
    {
        $query=    'SELECT DISTINCT eid 
            FROM '. self::$prefix ."metadata jm
            WHERE `key` = ?
               AND ((value=?) OR (? REGEXP CONCAT('^',value,'\$')))
               AND revisionid = (SELECT MAX(revisionid) FROM ".self::$prefix."metadata WHERE eid = jm.eid);";
        $data = array($key, $value, $value);
    	$st = $this->execute($query, $data);

        if ($st === false) {
            return 'error_db';
        }

        $rows = $st->fetchAll(PDO::FETCH_COLUMN, 0);
        return $rows;
    	
    }
    
    public function getAllEids($type)
    {
        $st = $this->execute(
            'SELECT DISTINCT eid 
            FROM '. self::$prefix ."entity
            WHERE `type` = ?",
            array($type)
        );

        if ($st === false) {
            return 'error_db';
        }

        $rows = $st->fetchAll(PDO::FETCH_COLUMN, 0);
        return $rows;
    	
    }
}

/**
 * The Janus REST interface
 * 
 * All ?method= methods are implemented by public member functions that
 * are prefixed with method_
 * 
 * @author ivo
 */
class sspmod_janus_Rest
{
    protected $_entityController;
    protected $_sspConfig = null;
    protected $_entityFinder = null;
    
    /**
     * Construct a new Rest handler.
     * @param sspmod_janus_EntityController $entityController Janus' entity 
     *        controller instance
     * @param SimpleSAML_Configuration $janusConfig The janus configuration
     */
    public function __construct($entityController, $janusConfig)
    {
        $this->_entityController = $entityController;
        $this->_sspConfig = $janusConfig;
    }
    
    /**
     * Metadata rest method. 
     * 
     * This implements the following REST call:
     * http://janusserver/simplesaml/module.php/janus/rest.php?method=metadata
     * 
     * @param array $request The request parameters (typically from $_REQUEST)
     *        The entries in $request for this method are:
     *        EntityID (req) - the URN of the entity for which to return 
     *                         metadata
     *        Keys (optional) - one or more comma separated keys of metadata 
     *                          to retrieve.
     *                          Note that keys that don't exist are silently 
     *                          discarded and won't be present in the output.
     * 
     * @return array An array of key/value pairs containing the metadata
     */
    public function method_metadata($request)
    {
    	if (!isset($request["entityid"])) {
    		throw new Exception("Parameter entityid is required");
    	}
    	
    	$result = array();
    	
        $eid = $this->_getEntityFinder()->findEid($request["entityid"]); 	
        
        $filter = array();
        if (isset($request["keys"])) {
            $filter = explode(",", $request["keys"]);
        }
        
        return $this->_getMetadata($eid, $filter);
      
    }
    
    /**
     * Rest method that determines if an SP is allowed to use a certain IdP. 
     * 
     * This implements the following REST call:
     * http://janusserver/simplesaml/module.php/
     *                    janus/rest.php?method=isconnectionallowed
     * 
     * Warning, the implementation is inefficient and doesn't follow the
     * practices of 'defensive programming' because of janus' current
     * blacklist approach. Method should be changed when whitelisting is 
     * implemented.
     * 
     * @param array $request The request parameters (typically from $_REQUEST)
     *        The entries in $request for this method are:
     *        SPEntityID (req)  - the URN of the SP entity to check
     *        IDPEntityID (req) - the URN of the IDP entity to check
     * 
     * @return array An array with a single key 'allowed' and a value that
     *               is either 'yes' or 'no'.
     */
    public function method_isconnectionallowed($request)
    {
    	$allowed = false;
    	
        if (!isset($request["spentityid"])||!isset($request["idpentityid"])) {
            throw new Exception("Both spentityid and idpentityid are required");
        }
        
        $idpEid = $this->_getEntityFinder()->findEid($request["idpentityid"]);    
      
        if (!is_null($idpEid)) {       
	        
	        $spEid = $this->_getEntityFinder()->findEid($request["spentityid"]);  

	        if ($spEid!=NULL) {
		        $this->_entityController->setEntity($spEid);
		    
		        if ($this->_entityController->getEntity()->getAllowedAll()=="yes") {
		            $allowed = true;
		        } else {
    		        $entities = $this->_entityController->getAllowedEntities();
    
                    // Check the whitelist
                    if (count($entities)) {
                        $allowed = (array_key_exists($request["idpentityid"], $entities));
                    } else {
                        // Check the blacklist
                        $entities = $this->_entityController->getBlockedEntities();
                        if (count($entities)) {
                            $allowed = (!array_key_exists($request["idpentityid"], $entities));
                        }
                        
                    }
		        }
	        }
        }    
        return array("allowed"=>($allowed?"yes":"no"));
    }
    
    /**
     * Unfinished implementation, awaits blacklist/whitelist implementation in janus.
     * For now, uses in efficient query that retrieves all eids (regardless of blacklist)
     * @param array $request The request parameters (typically from $_REQUEST)
     *        The entries in $request for this method are:
     * 
     *        keys (optional) - one or more comma separated keys of metadata 
     *                          to retrieve.
     *                          Note that keys that don't exist are silently 
     *                          discarded and won't be present in the output.  
     *        spentityid (optional) - List only those idps which are 
     *                                whitelisted against the SP identified by
     *                                this parameter
     *                      
     */
    public function method_idplist($request)
    {
        $filter = array();
        
        // here we have access to $this->_entityController->getBlockedEntities() 
        // but we need a whitelist approach.
        if (isset($request["keys"])) {
            $filter = explode(",", $request["keys"]);
            
            // We also need the identifier
            if (!in_array("entityID", $filter)) {
                $filter[] = "entityID";
            }
        }
        
        $spEntityId = NULL;
        if (isset($request["spentityid"])) { 
            $spEntityId = $request["spentityid"];
        }
        
        return $this->_getEntities("saml20-idp", $filter, $spEntityId);
    }
    
    /**
     * Unfinished implementation, awaits blacklist/whitelist implementation in janus.
     * For now, uses in efficient query that retrieves all eids (regardless of blacklist)
     * @param array $request The request parameters (typically from $_REQUEST)
     *        The entries in $request for this method are:
     * 
     *        keys (optional) - one or more comma separated keys of metadata 
     *                          to retrieve.
     *                          Note that keys that don't exist are silently 
     *                          discarded and won't be present in the output.
     */
    public function method_splist($request)
    {
        // here we have access to $this->_entityController->getBlockedEntities() 
        // but we need a whitelist approach.
        if (isset($request["keys"])) {
            $filter = explode(",", $request["keys"]);
            
            // We also need the identifier
            if (!in_array("entityID", $filter)) {
                $filter[] = "entityID";
            }
        }
        
        return $this->_getEntities("saml20-sp", $filter);
    }
    
    
    /**
     * Rest method that returns the attributes that can be released to an SP. 
     * 
     * This implements the following REST call:
     * http://janusserver/simplesaml/module.php/janus/rest.php?method=arp
     * 
     * @param array $request The request parameters (typically from $_REQUEST)
     *        The entries in $request for this method are:
     *        SPEntityID (req)  - the URN of the SP entity to check
     * 
     * @return array An array with 3 elements: 
     *                - Name: the name of the attribute release policy
     *                - Description
     *                - Attributes: an array of attribute names.
     */
    public function method_arp($request)
    {
        if (!isset($request["spentityid"])) {
            throw new Exception("Parameter spentityid is required");
        }
        $eid = $this->_getEntityFinder()->findEid($request["spentityid"]);    
         
        $this->_entityController->setEntity($eid);
    
        $arp = $this->_entityController->getArp();
        
        if ($arp==NULL) return NULL; // no arp set for this SP
        
        $result = array();
        $result["name"] = $arp->getName();
        $result["description"] = $arp->getDescription();         
        $result["attributes"] = $arp->getAttributes();
        
        return $result;
    }
    
   /**
     * Rest method for searching entities by metadata. 
     * 
     * This implements the following REST call:
     * http://janusserver/simplesaml/
     *               module.php/janus/rest.php?method=findidentifiersbymetadata
     * 
     * Note that this method is slightly inefficient, one reason is that it 
     * supports regular expressions (in the metadata data, not in the Value
     * parameter). Another is that we use janus' internal mechanism to retrieve
     * the identifier. This means that a separate query is performed for every
     * search result. (But since typically you'd be looking for one particular
     * enity, this shouldn't be a problem). Do cache heavily and call this 
     * method sparingly, please.
     * 
     * @param array $request The request parameters (typically from $_REQUEST)
     *        The entries in $request for this method are:
     *        Key (req)   - the key of the metadata entry you wish to search 
     *                      on.
     *        Value (req) - the value of the metadata entry you wish to search
     *                      for.
     * 
     * @return array An array of entity identifiers (URN) that match the query
     */
    public function method_findidentifiersbymetadata($request)
    {
        if (!isset($request["key"])||!isset($request["value"])) {
            throw new Exception("Both Key and Value parameters are required");
        }
        
        $result = array();
        
        $eids = $this->_getEntityFinder()->findEidsByMetadata($request["key"], $request["value"]);    
        
        foreach($eids as $eid) {
        
            $this->_entityController->setEntity($eid);
    
            $result[] = $this->_entityController->getEntity()->getEntityid();
        }
  
        
        return $result;
        
    }
    
    /**
     * Retrieve an instance of sspmod_janus_EntityFinder for lookups of eids.
     * @return sspmod_janus_EntityFinder The entity finder instance.
     */
    protected function _getEntityFinder()
    {
    	if ($this->_entityFinder==NULL) {
            $this->_entityFinder = new sspmod_janus_EntityFinder($this->_sspConfig);
        }
        return $this->_entityFinder;
            
    }
    
    /**
     * Get the metadata for one entity
     * @param String $eid The internal janus entity id
     * @param Array $keys Optional array of keys to be retrieved. Retrieves all metadata if empty
     * @return Array an associative array with key/value representations of the metadata
     */
    protected function _getMetadata($eid, $keys=array())
    {
    	$this->_entityController->setEntity($eid);
    
        $metadata = $this->_entityController->getMetadata();        
        
        $data = array();
        
        // Convert to key/value array
        foreach ($metadata as $metadataEntry) {
        	$data[$metadataEntry->getKey()] = $metadataEntry->getValue();
        }
        
        // Enrich with stuff that wasn't in the metadata itself
        $data["metadataUrl"] = $this->_entityController->getEntity()->getMetadataURL(); 
        $data["entityID"] = $this->_entityController->getEntity()->getEntityid();
        
        
        // Filter
        $result = array();
        foreach ($data as $key=>$value) {
            
            if (count($keys)==0 || in_array($key, $keys)) {
                $result[$key] = $value;
            }
            
        }
        
        return $result;
        
    }
    
    /** 
     * Retrieve all entity metadata for all entities of a certain type.
     * @param String $type Supported types: "saml20-idp" or "saml20-sp"
     * @param Array $keys optional list of metadata keys to retrieve. Retrieves all if blank
     * @param String $allowedEntityId if passed, returns only those entities that are 
     *                         whitelisted against the given entity
     * @return Array Associative array of all metadata. The key of the array is the identifier
     */
    protected function _getEntities($type, $keys=array(), $allowedEntityId=NULL)
    {
        $eids = array();
        
    	if (isset($allowedEntityId)) {
    	    $eid = $this->_getEntityFinder()->findEid($allowedEntityId);
    	    $this->_entityController->setEntity($eid);
    	    $this->_entityController->loadEntity();
    	    
    	    if ($this->_entityController->getEntity()->getAllowedAll()=="yes") {
    	        $eids = $this->_getEntityFinder()->getAllEids($type);
    	    } else {
    	        $entities = $this->_entityController->getAllowedEntities();

    	        // Check the whitelist
    	        if (count($entities)) {
        	        foreach($entities as $entityid=>$data) {
        	            $eids[] = $this->_getEntityFinder()->findEid($data["remoteentityid"]);
    	           }
    	        } else {
    	            // Check the blacklist
    	            $entities = $this->_entityController->getBlockedEntities();
    	            if (count($entities)) {
    	                $blockedEids = array();
    	                foreach ($entities as $entityid=>$data) {
    	                    $blockedEids[] = $this->_getEntityFinder()->findEid($data["remoteentityid"]);
    	                }
                  
    	                // Return all entities that are not in the blacklist
    	                $eids = array_diff($this->_getEntityFinder()->getAllEids($type), $blockedEids);
    	            }
    	            
    	        }
    	    }
    	    
    	} else {
            $eids = $this->_getEntityFinder()->getAllEids($type);    
    	}
    	
        $result = array();
    	
        
        foreach($eids as $eid) {
        
           $data = $this->_getMetadata($eid, $keys);
           $result[$data["entityID"]] = $data;          
      
        }
        return $result;
    }
}



/**
 * The main execution code, which is run whenever somebody requests rest.php.
 */

// Step 0: Initialize Janus and retrieve its config.
$session = SimpleSAML_Session::getInstance();
$janus_config = SimpleSAML_Configuration::getConfig('module_janus.php');


// Step 1: resolve the request to a method, and throw a 400 if it's not one 
// of the methods we know about.
$availableMethods = array("metadata", "isconnectionallowed", "idplist", "splist", "arp", "findidentifiersbymetadata");

if (!isset($_GET["method"]) || !in_array(strtolower($_GET["method"]), $availableMethods)) {
  header("HTTP/1.0 404 Not Found");
  die("Not Found    ");
}

// Step 2: Map the API method to a method in our rest object
$methodHandler = "method_".strtolower($_GET["method"]);


// Step 3: initialize janus' EntityController which will do all of the 
// underlying data retrieval for us.
$mcontroller = new sspmod_janus_EntityController($janus_config);

// Step 4: Instantiate our rest handler and dispatch the request
$rest = new sspmod_janus_Rest($mcontroller, $janus_config);
try {
	$result = $rest->$methodHandler($_REQUEST);
	
	// Step 5: Output the necessary headers and the result of the request as JSON.
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json'); // disable this line for debugging so you don't get the firefox download box everytime.
	
	echo json_encode($result);
} catch (Exception $e) {
	header("HTTP/1.0 400 Bad Request");
    die("Bad Request: ".$e->getMessage());
}
// Done!
