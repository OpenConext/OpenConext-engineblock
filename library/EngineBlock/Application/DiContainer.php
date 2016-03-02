<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use OpenConext\Component\EngineBlockMetadata\Container\ContainerInterface;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\CompositeMetadataRepository;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\InMemoryMetadataRepository;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;

class EngineBlock_Application_DiContainer extends Pimple implements ContainerInterface
{
    const METADATA_REPOSITORY                   = 'metadataRepository';
    const SUPER_GLOBAL_MANAGER                  = 'superGlobalManager';
    const OWN_ENTITIES_REPOSITORY               = 'ownMetadataRepository';
    const DOCTRINE_ENTITY_MANAGER               = 'entityManager';
    const ATTRIBUTE_METADATA                    = 'attributeMetadata';
    const ATTRIBUTE_DEFINITIONS_DENORMALIZED    = 'attributeDefinitionsDenormalized';
    const ATTRIBUTE_VALIDATOR                   = 'attributeValidator';

    /**
     * @var SymfonyContainerInterface
     */
    private $container;

    public function __construct(SymfonyContainerInterface $container)
    {
        $this->registerMetadataRepository();
        $this->registerEntityManager();
        $this->registerDenormalizedAttributeDefinitions();
        $this->registerAttributeMetadata();
        $this->registerAttributeValidator();

        $this->container = $container;
    }

    /**
     * @return EngineBlock_Corto_XmlToArray
     */
    public function getXmlConverter()
    {
        return $this->container->get('engineblock.compat.xml_converter');
    }

    /**
     * @return EngineBlock_Corto_Filter_Command_Factory
     */
    public function getFilterCommandFactory()
    {
        return $this->container->get('engineblock.compat.corto_filter_command_factory');
    }

    /**
     * @return EngineBlock_Mail_Mailer
     */
    public function getMailer()
    {
        return $this->container->get('engineblock.compat.mailer');
    }

    /**
     * @return EngineBlock_Database_ConnectionFactory
     */
    public function getDatabaseConnectionFactory()
    {
        return $this->container->get('engineblock.compat.database_connection_factory');
    }

    /**
     * @return EngineBlock_Corto_Model_Consent_Factory
     */
    public function getConsentFactory()
    {
        return $this->container->get('engineblock.compat.corto_model_consent_factory');
    }

    /**
     * It has been done with the check to be backwards compatible. Ideally this would be
     * hidden behind a compatibility layer rather than here and in the consumers of this
     * service, but since this will be removed in the future there is no need to
     * introduce additional code for this particular case.
     *
     * @return Zend_Cache_Backend_Apc
     */
    public function getApplicationCache()
    {
        $isApcEnabled = extension_loaded('apc') && ini_get('apc.enabled');
        if ($isApcEnabled) {
            return $this->container->get('engineblock.compat.zend.apc_cache');
        }

        return null;
    }

    /**
     * @return Janus_Client_CacheProxy
     */
    public function getServiceRegistryClient()
    {
        return $this->container->get('engineblock.compat.janus_cient');
    }

    /**
     * @return CompositeMetadataRepository
     */
    public function getMetadataRepository()
    {
        return $this[self::METADATA_REPOSITORY];
    }

    /**
     * @return EngineBlock_UserDirectory
     */
    public function getUserDirectory()
    {
        return $this->container->get('engineblock.compat.user_directory');
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
     * @deprecated since the themeis were introduced this should no longer be used.
     *
     * @return EngineBlock_AssetManager
     */
    public function getAssetManager()
    {
        return $this->container->get('engineblock.compat.asset_manager');
    }

    /**
     * @return EngineBlock_TimeProvider_Interface
     */
    public function getTimeProvider()
    {
        return $this->container->get('engineblock.compat.time_provider');
    }

    /**
     * @return EngineBlock_Saml2_IdGenerator
     */
    public function getSaml2IdGenerator()
    {
        return $this->container->get('engineblock.compat.saml2_id_generator');
    }

    /**
     * @return EngineBlock_Application_SuperGlobalManager|false
     */
    public function getSuperGlobalManager()
    {
        return false;
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

    /**
     * @deprecated will be replaced with different (incompatible) system in the future
     */
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

    /**
     * @deprecated will be replaced with different (incompatible) system in the future
     */
    public function getDenormalizedAttributeDefinitions()
    {
        return $this[self::ATTRIBUTE_DEFINITIONS_DENORMALIZED];
    }

    /**
     * @deprecated will be replaced with different (incompatible) system in the future
     */
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
     * @deprecated will be replaced with different (incompatible) system in the future
     * @return EngineBlock_Attributes_Metadata
     */
    public function getAttributeMetadata()
    {
        return $this[self::ATTRIBUTE_METADATA];
    }

    /**
     * @deprecated will be replaced with different (incompatible) system in the future
     */
    public function registerAttributeValidator()
    {
        $this[self::ATTRIBUTE_VALIDATOR] = function(EngineBlock_Application_DiContainer $container) {
            return new EngineBlock_Attributes_Validator(
                $container->getDenormalizedAttributeDefinitions(),
                new EngineBlock_Attributes_Validator_Factory()
            );
        };
    }

    /**
     * @deprecated will be replaced with different (incompatible) system in the future
     * @return EngineBlock_Attributes_Validator
     */
    public function getAttributeValidator()
    {
        return $this[self::ATTRIBUTE_VALIDATOR];
    }
}
