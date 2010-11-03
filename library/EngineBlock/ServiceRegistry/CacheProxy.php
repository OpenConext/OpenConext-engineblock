<?php

class EngineBlock_ServiceRegistry_CacheProxy 
{
    const DEFAULT_LIFETIME = 3600;

    public function __call($name, $arguments)
    {
        $client = $this->_getClient();

        $cache = $this->_getCacheFrontend();
        if (!$cache) {
            return call_user_method_array($name, $client, $arguments);
        }

        return $cache->call(array($client, $name), $arguments);
    }

    /**
     * @return Zend_Cache_Frontend_Function
     */
    protected function _getCacheFrontend()
    {
        $serviceRegistryConfig = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration()->serviceRegistry;
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
        return new EngineBlock_ServiceRegistry_Client();
    }
}
