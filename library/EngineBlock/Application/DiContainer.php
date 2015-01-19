<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use OpenConext\Component\EngineBlockMetadata\Container\ContainerInterface;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\CompositeMetadataRepository;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\InMemoryMetadataRepository;

class EngineBlock_Application_DiContainer extends Pimple implements ContainerInterface
{
    const XML_CONVERTER                         = 'xmlConverter';
    const CONSENT_FACTORY                       = 'consentFactory';
    const MAILER                                = 'mailer';
    const FILTER_COMMAND_FACTORY                = 'filterCommandFactory';
    const DATABASE_CONNECTION_FACTORY           = 'databaseConnectionFactory';
    const APPLICATION_CACHE                     = 'applicationCache';
    const SERVICE_REGISTRY_CLIENT               = 'serviceRegistryClient';
    const METADATA_REPOSITORY                   = 'metadataRepository';
    const ASSET_MANAGER                         = 'assetManager';
    const TIME                                  = 'dateTime';
    const SAML2_ID                              = 'id';
    const SUPER_GLOBAL_MANAGER                  = 'superGlobalManager';
    const OWN_ENTITIES_REPOSITORY               = 'ownMetadataRepository';
    const DOCTRINE_ENTITY_MANAGER               = 'entityManager';
    const ATTRIBUTE_METADATA                    = 'attributeMetadata';
    const ATTRIBUTE_DEFINITIONS_DENORMALIZED    = 'attributeDefinitionsDenormalized';

    public function __construct()
    {
        $this->registerXmlConverter();
        $this->registerConsentFactory();
        $this->registerMailer();
        $this->registerFilterCommandFactory();
        $this->registerDatabaseConnectionFactory();
        $this->registerApplicationCache();
        $this->registerServiceRegistryClient();
        $this->registerMetadataRepository();
        $this->registerAssetManager();
        $this->registerTimeProvider();
        $this->registerSaml2IdGenerator();
        $this->registerSuperGlobalManager();
        $this->registerEntityManager();
        $this->registerDenormalizedAttributeDefinitions();
        $this->registerAttributeMetadata();
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
     * @return CompositeMetadataRepository
     */
    public function getMetadataRepository()
    {
        return $this[self::METADATA_REPOSITORY];
    }

    protected function registerMetadataRepository()
    {
        $this[self::METADATA_REPOSITORY] = function (EngineBlock_Application_DiContainer $container)
        {
            $application = EngineBlock_ApplicationSingleton::getInstance();

            $repositoryConfigs = $application->getConfigurationValue('metadataRepository');
            if (!$repositoryConfigs instanceof Zend_Config) {
                throw new RuntimeException('metadataRepository config is not set or not multi-valued?');
            }

            $repositoriesConfig = $application->getConfigurationValue('metadataRepositories');
            if (!$repositoriesConfig instanceof Zend_Config) {
                throw new RuntimeException('metadataRepositories config is not set or not multi-valued?');
            }

            $repositoryConfigs  = $repositoryConfigs->toArray();
            $repositoriesConfig = $repositoriesConfig->toArray();

            $processedRepositoriesConfig = array();
            foreach ($repositoriesConfig as $repositoryId) {
                if (!isset($repositoryConfigs[$repositoryId])) {
                    throw new RuntimeException(
                        "metadataRepositories config mentions '$repositoryId', but no metadataRepository.$repositoryId found"
                    );
                }
                $processedRepositoriesConfig[] = $repositoryConfigs[$repositoryId];
            }

            return CompositeMetadataRepository::createFromConfig($processedRepositoriesConfig, $container);
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

    protected function registerEntityManager()
    {
        $this[self::DOCTRINE_ENTITY_MANAGER] = function () {
            $application = EngineBlock_ApplicationSingleton::getInstance();
            $logger = $application->getLogInstance();
            $engineBlockConfig = $application->getConfiguration();
            $mapper = new EngineBlock_Doctrine_ConfigMapper($logger);

            $driverConfig = $mapper->map($engineBlockConfig);

            // obtaining the entity manager
            return EntityManager::create(
                DriverManager::getConnection($driverConfig),
                Setup::createAnnotationMetadataConfiguration(
                    array(ENGINEBLOCK_FOLDER_VENDOR . "/openconext/engineblock-metadata/src"),
                    true,
                    null,
                    null,
                    false
                )
            );
        };
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this[self::DOCTRINE_ENTITY_MANAGER];
    }

    private function registerDenormalizedAttributeDefinitions()
    {
        $this[self::ATTRIBUTE_DEFINITIONS_DENORMALIZED] = function() {
            $application = EngineBlock_ApplicationSingleton::getInstance();
            $definitionFile = $application->getConfigurationValue(
                'attributeDefinitionFile',
                ENGINEBLOCK_FOLDER_APPLICATION . 'configs/attributes.json'
            );
            $definitionFileContent = file_get_contents($definitionFile);
            $definitions = json_decode($definitionFileContent, true);

            $denormalizer = new EngineBlock_Attributes_Definition_Denormalizer();
            return $denormalizer->denormalize($definitions);
        };
    }

    public function getDenormalizedAttributeDefinitions()
    {
        return $this[self::ATTRIBUTE_DEFINITIONS_DENORMALIZED];
    }

    private function registerAttributeMetadata()
    {
        $this[self::ATTRIBUTE_METADATA] = function(EngineBlock_Application_DiContainer $container) {
            return new EngineBlock_Attributes_Metadata(
                $container->getDenormalizedAttributeDefinitions(),
                EngineBlock_ApplicationSingleton::getInstance()->getLogInstance()
            );
        };
    }

    /**
     * @return EngineBlock_Attributes_Metadata
     */
    public function getAttributeMetadata()
    {
        return $this[self::ATTRIBUTE_METADATA];
    }
}
