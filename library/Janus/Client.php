<?php

/**
 * Implementation of the Engine Block internal Service Registry interface.
 */
class Janus_Client implements Janus_Client_Interface
{
    /**
     * The REST client used to communicate to the Janus service registry.
     * @var $_restClient EngineBlock_Rest_Client
     */
    protected $_restClient = null;

    /**
     * Get full information for a given entity.
     *
     * Note that this will throw a EngineBlock_Exception if there is no entity for the given entityid.
     *
     * @param string $entityId
     * @return mixed
     * @throws EngineBlock_Exception
     */
    public function getEntity($entityId)
    {
        return $this->getRestClient()->getEntity()
                                        ->entityid($entityId)
                                        ->get();
    }
    
    /**
     * Retrieve all known metadata about an (SP or IDP) entity.
     * @param string $entityId The URN of the entity 
     * @return array An array with key/value pairs.
     */
    public function getMetadata($entityId) 
    {
        $response = $this->getRestClient()->getMetadata()
                                           ->entityid($entityId)
                                           ->get();
        return $response;     
    }
    
    /**
     * Retrieve a particular metadata value of an (SP or IDP) entity.
     * @param string $entityId The URN of the entity
     * @param string $key The name of the metadata entry to retrieve
     * @return string The value for the requested metadata key
     */
    public function getMetaDataForKey($entityId, $key)
    {
        $response = $this->getRestClient()->getMetadata()
                                          ->entityid($entityId)
                                          ->keys($key)
                                          ->get();    
        if (isset($response[$key])) {
            return $response[$key];
        }
        return null;
    }
    
    /**
     * Retrieve a set of metadata values of an (SP or IDP) entity.
     * @param string $entityId The URN of the entity
     * @param array $keys An array of keys to retrieve
     * @return array An associative array of values, indexed by key
     */
    public function getMetaDataForKeys($entityId, $keys)
    {
        $response = $this->getRestClient()->getMetadata()
                                           ->entityid($entityId)
                                           ->keys(implode(",", $keys))
                                           ->get();
        return $response;
    }    
    
    /**
     * Validate if an SP and IDP have a relationship, e.g. when a user
     * requests access to a certain SP, this can be used to determine whether
     * he's allowed to authenticate against a selected IDP.
     * @param string $spEntityId The URN of the SP entity
     * @param string $idpEntityId The URN of the IDP entity
     * @return boolean True if there's a relationship, false if not.
     */
    public function isConnectionAllowed($spEntityId, $idpEntityId)
    {
        $response = $this->getRestClient()->isConnectionAllowed()
                                           ->spentityid($spEntityId)
                                           ->idpentityid($idpEntityId)
                                           ->get();
        return ((isset($response[0]) && $response[0]==true) || (isset($response["allowed"]) && $response["allowed"]=="yes"));
    }

    /**
     * Retrieve the allowed IDPs for an SP. The SP is only
     * allowed to make connections to the retrieved IDP's.
     *
     * @param string $spEntityId the URN of the SP entity.
     * @return array containing the URN's of the IDP's that this SP is allowed to make a connection to.
     */
    public function getAllowedIdps($spEntityId)
    {
        $response = $this->getRestClient()->getAllowedIdps()
                                           ->spentityid($spEntityId)
                                           ->get();
        return $response;
    }

    /**
     * Retrieve the allowed SP's for an IDP. The IDP is only
     * allowed to make connections to the retrieved SP's.
     *
     * @param string $spEntityId the URN of the IDP entity.
     * @return array containing the URN's of the SP's that this IDP is allowed to make a connection to.
     */
    public function getAllowedSps($idpEntityId)
    {
        $response = $this->getRestClient()->getAllowedSps()
                                           ->idpentityid($idpEntityId)
                                           ->get();
        return $response;
    }

    /**
     * Retrieve the Attribute Release Policy for a certain Service Provider.
     * 
     * @param string $spEntityId The URN of the service provider
     * @return array An associative array with 3 keys:
     *               - name: the name of the attribute release policy
     *               - description: an optional description
     *               - attributes: an array containing all the attribute names
     *                 that may be released to the given SP.
     */
    public function getArp($spEntityId)
    {
        $response = $this->getRestClient()->arp()
                                          ->entityid($spEntityId)
                                          ->get();
        return $response;
    }
    
    /**
     * Retrieve a list of metadata values of all available
     * IDP entities.
     * @param array $keys An array of keys to retrieve. Retrieves
     *                    all available keys if omited or empty
     * @param String $forSpEntityId An optional identifier of an SP
     *               If present, idplist will return a list of only the
     *               idps that this sp is allowed to authenticate against.
     * @return array An associative array of values, indexed by IDP 
     *               identifier. Each value is another associative 
     *               array with key/value pairs containing the metadata.
     */
    public function getIdpList($keys=array(), $forSpEntityId=null)
    {
        $response = $this->getRestClient()->getIdpList()
                                           ->keys(implode(",", $keys))
                                           ->spentityid($forSpEntityId)
                                           ->get();                                        
        return $response;                                          
    }
    
    /**
     * Retrieve a list of metadata values of all available
     * SP entities.
     * @param array $keys An array of keys to retrieve. Retrieves
     *                    all available keys if omited or empty
     * @return array An associative array of values, indexed by SP 
     *               identifier. Each value is another associative 
     *               array with key/value pairs containing the metadata.
     */
    public function getSpList($keys=array())
    {
        $response = $this->getRestClient()->getSpList()
                                           ->keys(implode(",", $keys))
                                           ->get();
        return $response;                                         
    }
    
    /**
     * Find entities based on metadata.
     * 
     * Finds the identifiers (URNS) of all SPs/IDPs that match a certain
     * metadata value. The rest webservice that's behind this call supports
     * regular expressions in the metadata values in its database. So you 
     * can pass "www.google.com" as value to this function and match 
     * entities that have '.*\.google\.com in their url:en metadata field.
     *  
     * @param string $key The key you want to match against
     * @param string $value The value you want to match against
     * @return array An array of URNS of entities that match the request.
     */
    public function findIdentifiersByMetadata($key, $value)
    {
        $response = $this->getRestClient()->findIdentifiersByMetadata()
                                          ->key($key)
                                          ->value($value)
                                          ->get();
        return $response;
    }

    /**
     * @return Janus_Rest_Client
     * @throws Janus_Client_Exception
     */
    public function getRestClient()
    {
        if ($this->_restClient) {
            return $this->_restClient;
        }
        $config = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->serviceRegistry;
        $location = $config->location;
        if (!$location) {
            throw new Janus_Client_Exception(
                'No Service Registry location provided! Please set "serviceRegistry.location" in your application configuration.'
            );
        }

        $user = $config->user;
        if (!$user) {
            throw new Janus_Client_Exception(
                'No Service Registry user provided! Please set "serviceRegistry.user" in your application configuration.'
            );
        }

        $secret = $config->user_secret;
        if (!$secret) {
            throw new Janus_Client_Exception(
                'No Service Registry user secret provided! Please set "serviceRegistry.user_secret" in your application configuration.'
            );
        }
        $this->_restClient = new Janus_Rest_Client($location, $user, $secret);
        return $this->_restClient;
    }
    
    /**
     * Method for injecting a rest client other than the default EngineBlock_Rest_Client, which is useful for
     * unit tests. Should not generally be used in other situations as getRestClient() will construct
     * a default EngineBlock_Rest_Client if necessary.
     * @param Zend_Rest_Client $client The class will work as long as the $client has Zend_Rest_Client's
     *                                 interface, even though the default is a EngineBlock_Rest_Client. 
     */
    public function setRestClient($client) 
    {
        $this->_restClient = $client;
    }
}
