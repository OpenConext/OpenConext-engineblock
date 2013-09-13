<?php
class EngineBlock_Application_DiContainer extends Pimple
{
    const XML_CONVERTER = 'xmlConverter';
    const CONSENT_FACTORY = 'consentFactory';
    const MAILER = 'mailer';
    const FILTER_COMMAND_FACTORY = 'filterCommandFactory';
    const DATABASE_CONNECTION_FACTORY = 'databaseConnectionFactory';
    const REDIS_CLIENT = 'redisClient';
    const APPLICATION_CACHE = 'applicationCache';
    const SERVICE_REGISTRY_CLIENT = 'serviceRegistryClient';
    const SERVICE_REGISTRY_ADAPTER = 'serviceRegistryAdapter';

    public function __construct()
    {
        $this->registerXmlConverter();
        $this->registerConsentFactory();
        $this->registerMailer();
        $this->registerFilterCommandFactory();
        $this->registerDatabaseConnectionFactory();
        $this->registerRedisClient();
        $this->registerApplicationCache();
        $this->registerServiceRegistryClient();
        $this->registerServiceRegistryAdapter();
    }

    protected function registerXmlConverter()
    {
        $this[self::XML_CONVERTER] = $this->share(function (EngineBlock_Application_DiContainer $container)
        {
            return new EngineBlock_Corto_XmlToArray();
        });
    }

    /**
     * @return EngineBlock_Corto_Model_Consent_Factory
     */
    public function getConsentFactory()
    {
        return $this[self::CONSENT_FACTORY];
    }

    protected function registerConsentFactory()
    {
        $this[self::CONSENT_FACTORY] = $this->share(function (EngineBlock_Application_DiContainer $container)
        {
            return new EngineBlock_Corto_Model_Consent_Factory();
        });
    }

    protected function registerMailer()
    {
        $this[self::MAILER] = $this->share(function (EngineBlock_Application_DiContainer $container)
        {
            return new EngineBlock_Mail_Mailer();
        });
    }

    /**
     * @return EngineBlock_Corto_Filter_Command_Factory
     */
    public function getFilterCommandFactory()
    {
        return $this[self::FILTER_COMMAND_FACTORY];
    }

    protected function registerFilterCommandFactory()
    {
        $this[self::FILTER_COMMAND_FACTORY] = $this->share(function (EngineBlock_Application_DiContainer $container)
        {
            return new EngineBlock_Corto_Filter_Command_Factory();
        });
    }

    /**
     * @return EngineBlock_Database_ConnectionFactory
     */
    public function getDatabaseConnectionFactory()
    {
        return $this[self::DATABASE_CONNECTION_FACTORY];
    }

    protected function registerDatabaseConnectionFactory()
    {
        $this[self::DATABASE_CONNECTION_FACTORY] = $this->share(function (EngineBlock_Application_DiContainer $container)
        {
            return new EngineBlock_Database_ConnectionFactory();
        });
    }

    /**
     * @return Redis
     */
    public function getRedisClient()
    {
        return $this[self::REDIS_CLIENT];
    }

    protected function registerRedisClient()
    {
        $this[self::REDIS_CLIENT] = $this->share(function (EngineBlock_Application_DiContainer $container)
        {
            $redisClient = new Redis();
            $redisClient->connect('127.0.0.1');

            return $redisClient;
        });
    }

    /**
     * @return Zend_Cache_Backend_Apc
     */
    public function getApplicationCache()
    {
        return $this[self::APPLICATION_CACHE];
    }

    protected function registerApplicationCache()
    {
        $this[self::APPLICATION_CACHE] = $this->share(function (EngineBlock_Application_DiContainer $container)
        {
            $isApcEnabled = extension_loaded('apc') && ini_get('apc.enabled');
            if ($isApcEnabled) {
                return new Zend_Cache_Backend_Apc();
            }
        });
    }

    /**
     * @return Janus_Client_CacheProxy()
     */
    public function getServiceRegistryClient()
    {
        return $this[self::SERVICE_REGISTRY_CLIENT];
    }

    protected function registerServiceRegistryClient()
    {
        $this[self::SERVICE_REGISTRY_CLIENT] = $this->share(function (EngineBlock_Application_DiContainer $container)
        {
            return new Janus_Client_CacheProxy();
        });
    }

    /**
     * @return EngineBlock_Corto_ServiceRegistry_Adapter()
     */
    public function getServiceRegistryAdapter()
    {
        return $this[self::SERVICE_REGISTRY_ADAPTER];
    }

    protected function registerServiceRegistryAdapter()
    {
        $this[self::SERVICE_REGISTRY_ADAPTER] = $this->share(function (EngineBlock_Application_DiContainer $container)
        {
            return new EngineBlock_Corto_ServiceRegistry_Adapter($container->getServiceRegistryClient());
        });
    }
}