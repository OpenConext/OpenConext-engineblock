<?php

use OpenConext\Component\EngineBlockMetadata\JanusRestV1\RestClientInterface;

/**
 * A Caching Proxy for the Service Registry, will cache all function calls.
 *
 * Can even detect Service Registry problems and chug along on the (stale) cache.
 */
class Janus_Client_CacheProxy implements RestClientInterface
{
    const DEFAULT_LIFETIME = 5;

    /**
     * Retrieve the allowed IDPs for an SP. The SP is only
     * allowed to make connections to the retrieved IDP's.
     *
     * @param string $spEntityId the URN of the SP entity.
     * @return array containing the URN's of the IDP's that this SP is allowed to make a connection to.
     */
    public function getAllowedIdps($spEntityId)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * Get full information for a given entity.
     *
     * @param $entityId
     * @return array
     */
    public function getEntity($entityId)
    {
        return $this->__call(__FUNCTION__, func_get_args());
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
    public function getIdpList($keys = array(), $forSpEntityId = null)
    {
        return $this->__call(__FUNCTION__, func_get_args());
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
    public function getSpList($keys = array())
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $propertyName
     * @param string $propertyValue
     * @return array
     */
    public function findIdentifiersByMetadata($propertyName, $propertyValue)
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws Janus_Client_CacheProxy_Exception
     * @throws Janus_Client_Exception
     * @throws void
     */
    public function __call($name, $arguments)
    {
        $client = $this->_getClient();

        $cache = $this->_getCacheFrontend();
        if (!$cache) {
            return call_user_func_array(array($client, $name), $arguments);
        }

        // Clone the original client because calling it will alter the $client object
        // making it impossible to reuse stale cache
        $originalClient = clone $client;

        try {
            return $cache->call(array($client, $name), $arguments);

        } catch(Exception $e) { // Whoa, something went wrong, maybe the SR is down? Trying to use stale cache...

            $httpClient = $client->getRestClient()->getHttpClient();
            $logContext = array('http_request' => $httpClient->getLastRequest());

            $response = $httpClient->getLastResponse();
            if ($response) {
                $logContext['http_response'] = $response->getHeadersAsString() . PHP_EOL . $response->getBody();
            }

            $application = EngineBlock_ApplicationSingleton::getInstance();
            $application->getLogInstance()->warning('Error while accessing Janus', $logContext);

            $e = new Janus_Client_CacheProxy_Exception(
                "Unable to access JANUS?!? Using stale cache",
                EngineBlock_Exception::CODE_WARNING,
                $e
            );
            $application->reportError($e);

            // Give any stale cache some more time
            $callback = array($originalClient, $name);
            $cacheId = $cache->makeId($callback, $arguments);
            $cacheBackend = $cache->getBackend();
            $data = $cacheBackend->load($cacheId, TRUE);
            if ($data !== false) {
                $cacheBackend->save($data, $cacheId, array(), self::DEFAULT_LIFETIME);

                try {
                    // And try to use that cache.
                    return $cache->call($callback, $arguments);
                } catch (Exception $e) {
                    throw new Janus_Client_CacheProxy_Exception(
                        "Unable to contact JANUS and unable to reuse stale cache!",
                        EngineBlock_Exception::CODE_ALERT,
                        $e
                    );
                }
            }

            throw new Janus_Client_CacheProxy_Exception(
                "Unable to contact JANUS and no stale cache found for possible reuse!",
                EngineBlock_Exception::CODE_ALERT,
                $e
            );
        }
    }

    /**
     * @return Zend_Cache_Frontend_Function
     */
    protected function _getCacheFrontend()
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();
        $serviceRegistryConfig = $application->getConfiguration()->serviceRegistry;
        if (!isset($serviceRegistryConfig->caching)) {
            return null;
        }
        $cachingConfiguration = $serviceRegistryConfig->caching;

        $backendCaching        = $cachingConfiguration->backend->get('name', 'File');
        $backendCachingOptions = $cachingConfiguration->backend->options->toArray();

        $cache = Zend_Cache::factory(
            'Function',
            $backendCaching,
            array(),
            $backendCachingOptions
        );
        $cache->setLifetime($cachingConfiguration->get('lifetime', self::DEFAULT_LIFETIME));
        return $cache;
    }

    protected function _getClient()
    {
        return new Janus_Client();
    }
}
