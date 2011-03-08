<?php

class EngineBlock_ServiceRegistry_CacheProxy_Exception extends EngineBlock_Exception
{
}

/**
 * A Caching Proxy for the Service Registry, will cache all function calls.
 *
 * Can even detect Sercvice Registry problems and chug along on the (stale) cache. 
 */
class EngineBlock_ServiceRegistry_CacheProxy
{
    const DEFAULT_LIFETIME = 5;

    public function __call($name, $arguments)
    {
        $client = $this->_getClient();

        $cache = $this->_getCacheFrontend();
        if (!$cache) {
            return call_user_method_array($name, $client, $arguments);
        }

        // Clone the original client because calling it will alter the $client object
        // making it impossible to reuse stale cache
        $originalClient = clone $client;

        try {
            return $cache->call(array($client, $name), $arguments);

        } catch(Exception $e) { // Whoa, something went wrong, maybe the SR is down? Trying to use stale cache...
            if (floatval(phpversion()) > 5.3) {
                $e = new EngineBlock_ServiceRegistry_CacheProxy_Exception("Service Registry problems?!?", 0, $e);
            }
            else {
                $e = new EngineBlock_ServiceRegistry_CacheProxy_Exception("Service Registry problems?!?", 0);
            }
            EngineBlock_ApplicationSingleton::getInstance()->reportError($e);

            // Give any stale cache some more time
            $callback = array($originalClient, $name);
            $cacheId = $cache->makeId($callback, $arguments);
            $cacheBackend = $cache->getBackend();
            $data = $cacheBackend->load($cacheId, TRUE);
            if ($data !== false) {
                $cacheBackend->save($data, $cacheId, array(), self::DEFAULT_LIFETIME);
            }

            // And try to use that cache.
            return $cache->call($callback, $arguments);
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
        if (isset($backendCachingOptions['file_name_prefix'])) {
            $backendCachingOptions['file_name_prefix'] .= '_' . $application->getApplicationEnvironmentId();
        }

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
        return new EngineBlock_ServiceRegistry_Client();
    }
}
