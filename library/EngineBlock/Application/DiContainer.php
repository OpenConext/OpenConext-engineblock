<?php

use Doctrine\ORM\EntityManager;
use OpenConext\Component\EngineBlockMetadata\Container\ContainerInterface;
use OpenConext\Component\EngineBlockMetadata\MetadataRepository\CompositeMetadataRepository;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;

class EngineBlock_Application_DiContainer extends Pimple implements ContainerInterface
{
    const METADATA_REPOSITORY                   = 'metadataRepository';
    const SUPER_GLOBAL_MANAGER                  = 'superGlobalManager';
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
        $this->registerDenormalizedAttributeDefinitions();
        $this->registerAttributeMetadata();
        $this->registerAttributeValidator();

        $this->container = $container;
    }

    /**
     * @return \OpenConext\EngineBlockBridge\Logger\AuthenticationLoggerAdapter
     */
    public function getAuthenticationLogger()
    {
        return $this->container->get('engineblock.bridge.authentication_logger_adapter');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getSymfonyRequest()
    {
        return $this->container->get('request');
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
     * @return \OpenConext\EngineBlockBundle\AttributeAggregation\AttributeAggregationClientInterface
     */
    public function getAttributeAggregationClient()
    {
        return $this->container->get('engineblock.attribute_aggregation.client');
    }

    /**
     * @return CompositeMetadataRepository
     */
    public function getMetadataRepository()
    {
        return $this[self::METADATA_REPOSITORY];
    }

    /**
     * @return \OpenConext\EngineBlockBridge\Authentication\Repository\UserDirectoryAdapter
     */
    public function getUserDirectory()
    {
        return $this->container->get('engineblock.bridge.authentication.user_directory');
    }

    /**
     * @return OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration
     */
    public function getFeatureConfiguration()
    {
        return $this->container->get('engineblock.features');
    }

    /**
     * @return OpenConext\EngineBlockBundle\Authentication\AuthenticationLoopGuard
     */
    public function getAuthenticationLoopGuard()
    {
        return $this->container->get('engineblock.authentication.authentication_loop_guard');
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
       return 'EngineBlock_Ssp_sspmod_saml_SymfonyRequestUriMessage';
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->container->get('doctrine.orm.engineblock_entity_manager');
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
                ENGINEBLOCK_FOLDER_APPLICATION . 'configs/attributes-v2.2.0.json'
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

    public function getCutoffPointForShowingUnfilteredIdps()
    {
        return $this->container->getParameter('wayf.cutoff_point_for_showing_unfiltered_idps');
    }

    /**
     * @return object|\Symfony\Component\HttpFoundation\Session\Session
     */
    public function getSession()
    {
        return $this->container->get('session');
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getSymfonyContainer()
    {
        return $this->container;
    }

    public function getPdpClient()
    {
        return $this->container->get('engineblock.pdp.pdp_client');
    }

    public function getFunctionalTestingPdpClient()
    {
        return $this->container->get('engineblock.functional_testing.fixture.pdp_client');
    }

    /**
     * @return \OpenConext\EngineBlockBundle\Localization\LocaleProvider
     */
    public function getLocaleProvider()
    {
        return $this->container->get('engineblock.locale_provider');
    }
}
