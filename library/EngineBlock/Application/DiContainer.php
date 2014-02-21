<?php
class EngineBlock_Application_DiContainer extends Pimple
{
    const XML_CONVERTER = 'xmlConverter';
    const CONSENT_FACTORY = 'consentFactory';
    const MAILER = 'mailer';
    const FILTER_COMMAND_FACTORY = 'filterCommandFactory';
    const DATABASE_CONNECTION_FACTORY = 'databaseConnectionFactory';
    const APPLICATION_CACHE = 'applicationCache';
    const SERVICE_REGISTRY_CLIENT = 'serviceRegistryClient';
    const SERVICE_REGISTRY_ADAPTER = 'serviceRegistryAdapter';
    const ASSET_MANAGER = 'assetManager';
    const TIME = 'dateTime';
    const SAML2_ID = 'id';
    const SUPER_GLOBAL_MANAGER = 'superGlobalManager';

    public function __construct()
    {
        $this->registerXmlConverter();
        $this->registerConsentFactory();
        $this->registerMailer();
        $this->registerFilterCommandFactory();
        $this->registerDatabaseConnectionFactory();
        $this->registerApplicationCache();
        $this->registerServiceRegistryClient();
        $this->registerServiceRegistryAdapter();
        $this->registerAssetManager();
        $this->registerTimeProvider();
        $this->registerSaml2IdGenerator();
        $this->registerSuperGlobalManager();
    }

    protected function registerXmlConverter()
    {
        $this[self::XML_CONVERTER] = function (EngineBlock_Application_DiContainer $container)
        {
            return new EngineBlock_Corto_XmlToArray();
        };
    }

    protected function registerConsentFactory()
    {
        $this[self::CONSENT_FACTORY] = function (EngineBlock_Application_DiContainer $container)
        {
            return new EngineBlock_Corto_Model_Consent_Factory(
                $container[$container::FILTER_COMMAND_FACTORY],
                $container[$container::DATABASE_CONNECTION_FACTORY]
            );
        };
    }

    protected function registerMailer()
    {
        $this[self::MAILER] = function (EngineBlock_Application_DiContainer $container)
        {
            return new EngineBlock_Mail_Mailer();
        };
    }

    protected function registerFilterCommandFactory()
    {
        $this[self::FILTER_COMMAND_FACTORY] = function (EngineBlock_Application_DiContainer $container)
        {
            return new EngineBlock_Corto_Filter_Command_Factory();
        };
    }

    protected function registerDatabaseConnectionFactory()
    {
        $this[self::DATABASE_CONNECTION_FACTORY] = function (EngineBlock_Application_DiContainer $container)
        {
            return new EngineBlock_Database_ConnectionFactory();
        };
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
        $this[self::APPLICATION_CACHE] = function (EngineBlock_Application_DiContainer $container)
        {
            $isApcEnabled = extension_loaded('apc') && ini_get('apc.enabled');
            if ($isApcEnabled) {
                return new Zend_Cache_Backend_Apc();
            }
        };
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
        $this[self::SERVICE_REGISTRY_CLIENT] = function ()
        {
            return new Janus_Client_CacheProxy();
        };
    }

    /**
     * @return EngineBlock_Corto_ServiceRegistry_JanusRestAdapter()
     */
    public function getServiceRegistryAdapter()
    {
        return $this[self::SERVICE_REGISTRY_ADAPTER];
    }

    protected function registerServiceRegistryAdapter()
    {
        $this[self::SERVICE_REGISTRY_ADAPTER] = function (EngineBlock_Application_DiContainer $container)
        {
            return new EngineBlock_Corto_ServiceRegistry_JanusRestAdapter($container->getServiceRegistryClient());
        };
    }

    /**
     * @return EngineBlock_AssetManager
     */
    public function getAssetManager()
    {
        return $this[self::ASSET_MANAGER];
    }

    protected function registerAssetManager()
    {
        $this[self::ASSET_MANAGER] = function ()
        {
            return new EngineBlock_AssetManager();
        };
    }

    /**
     * @return EngineBlock_TimeProvider_Interface
     */
    public function getTimeProvider()
    {
        return $this[self::TIME];
    }

    protected function registerTimeProvider()
    {
        $this[self::TIME] = function ()
        {
            return new EngineBlock_TimeProvider_Default();
        };
    }

    /**
     * @return EngineBlock_Saml2_IdGenerator_Interface
     */
    public function getSaml2IdGenerator()
    {
        return $this[self::SAML2_ID];
    }

    protected function registerSaml2IdGenerator()
    {
        $this[self::SAML2_ID] = function()
        {
            return new EngineBlock_Saml2_IdGenerator_Default();
        };
    }

    /**
     * @return EngineBlock_Application_SuperGlobalManager
     */
    public function getSuperGlobalManager()
    {
        return $this[self::SUPER_GLOBAL_MANAGER];
    }

    protected function registerSuperGlobalManager()
    {
        $this[self::SUPER_GLOBAL_MANAGER] = false;
    }

    /**
     * Classname to use for Message utilities.
     *
     * @return string
     */
    public function getMessageUtilClassName()
    {
       return 'sspmod_saml_Message';
    }
}
