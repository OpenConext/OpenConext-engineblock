<?php

/**
 * Implementation of the Engine Block internal Service Registry interface.
 */
interface Janus_Client_Interface
{
    /**
     * Get full information for a given entity.
     *
     * @param $entityId
     * @return mixed
     */
    public function getEntity($entityId);

    /**
     * Retrieve all known metadata about an (SP or IDP) entity.
     * @param string $entityId The URN of the entity
     * @return array An array with key/value pairs.
     */
    public function getMetadata($entityId);

    /**
     * Retrieve the allowed IDPs for an SP. The SP is only
     * allowed to make connections to the retrieved IDP's.
     *
     * @param string $spEntityId the URN of the SP entity.
     * @return array containing the URN's of the IDP's that this SP is allowed to make a connection to.
     */
    public function getAllowedIdps($spEntityId);

    /**
     * Retrieve a particular metadata value of an (SP or IDP) entity.
     * @param string $entityId The URN of the entity
     * @param string $key The name of the metadata entry to retrieve
     * @return string The value for the requested metadata key
     */
    public function getMetaDataForKey($entityId, $key);

    /**
     * Retrieve a list of metadata values of all available
     * SP entities.
     * @param array $keys An array of keys to retrieve. Retrieves
     *                    all available keys if omited or empty
     * @return array An associative array of values, indexed by SP
     *               identifier. Each value is another associative
     *               array with key/value pairs containing the metadata.
     */
    public function getSpList($keys = array());

    /**
     * Retrieve the allowed SP's for an IDP. The IDP is only
     * allowed to make connections to the retrieved SP's.
     *
     * @param string $idpEntityId the URN of the IDP entity.
     * @return array containing the URN's of the SP's that this IDP is allowed to make a connection to.
     */
    public function getAllowedSps($idpEntityId);

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
    public function getArp($spEntityId);

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
    public function getIdpList($keys = array(), $forSpEntityId = null);

    /**
     * Retrieve a set of metadata values of an (SP or IDP) entity.
     * @param string $entityId The URN of the entity
     * @param array $keys An array of keys to retrieve
     * @return array An associative array of values, indexed by key
     */
    public function getMetaDataForKeys($entityId, $keys);

    /**
     * Validate if an SP and IDP have a relationship, e.g. when a user
     * requests access to a certain SP, this can be used to determine whether
     * he's allowed to authenticate against a selected IDP.
     * @param string $spEntityId The URN of the SP entity
     * @param string $idpEntityId The URN of the IDP entity
     * @return boolean True if there's a relationship, false if not.
     */
    public function isConnectionAllowed($spEntityId, $idpEntityId);

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
    public function findIdentifiersByMetadata($key, $value);

    /**
     * @return Janus_Rest_Client
     * @throws Janus_Client_Exception
     */
    public function getRestClient();

    /**
     * Method for injecting a rest client other than the default EngineBlock_Rest_Client, which is useful for
     * unit tests. Should not generally be used in other situations as getRestClient() will construct
     * a default EngineBlock_Rest_Client if necessary.
     * @param Zend_Rest_Client $client The class will work as long as the $client has Zend_Rest_Client's
     *                                 interface, even though the default is a EngineBlock_Rest_Client.
     */
    public function setRestClient($client);
}
