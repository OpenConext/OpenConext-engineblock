<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use Doctrine\ORM\EntityManager;
use OpenConext\EngineBlock\Metadata\Factory\Factory\ServiceProviderFactory;
use OpenConext\EngineBlock\Metadata\LoaRepository;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlock\Service\MfaHelperInterface;
use OpenConext\EngineBlock\Service\ReleaseAsEnforcer;
use OpenConext\EngineBlock\Service\TimeProvider\TimeProviderInterface;
use OpenConext\EngineBlock\Stepup\StepupEntityFactory;
use OpenConext\EngineBlock\Stepup\StepupGatewayCallOutHelper;
use OpenConext\EngineBlock\Validator\AllowedSchemeValidator;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

class EngineBlock_Application_DiContainer extends \Pimple\Container
{
    const ATTRIBUTE_METADATA                    = 'attributeMetadata';
    const ATTRIBUTE_DEFINITIONS_DENORMALIZED    = 'attributeDefinitionsDenormalized';
    const ATTRIBUTE_VALIDATOR                   = 'attributeValidator';

    /**
     * @var SymfonyContainerInterface
     */
    protected $container;

    public function __construct(SymfonyContainerInterface $container)
    {
        $this->registerDenormalizedAttributeDefinitions();
        $this->registerAttributeMetadata();
        $this->registerAttributeValidator();

        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return (string) $this->container->getParameter('hostname');
    }

    /**
     * @return array
     */
    public function getPhpSettings()
    {
        return (array) $this->container->getParameter('php_settings');
    }

    /**
     * @return \OpenConext\EngineBlockBridge\Logger\AuthenticationLoggerAdapter
     */
    public function getAuthenticationLogger()
    {
        return $this->container->get(\OpenConext\EngineBlockBridge\Logger\AuthenticationLoggerAdapter::class);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getSymfonyRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    /**
     * @return EngineBlock_Corto_XmlToArray
     */
    public function getXmlConverter(): EngineBlock_Corto_XmlToArray
    {
        return $this->container->get('engineblock.compat.xml_converter');
    }

    /**
     * @return EngineBlock_Corto_Filter_Command_Factory
     */
    public function getFilterCommandFactory(): EngineBlock_Corto_Filter_Command_Factory
    {
        return $this->container->get('engineblock.compat.corto_filter_command_factory');
    }

    /**
     * @return EngineBlock_Database_ConnectionFactory
     */
    public function getDatabaseConnectionFactory(): EngineBlock_Database_ConnectionFactory
    {
        return $this->container->get('engineblock.compat.database_connection_factory');
    }

    /**
     * @return EngineBlock_Corto_Model_Consent_Factory
     */
    public function getConsentFactory(): EngineBlock_Corto_Model_Consent_Factory
    {
        return $this->container->get('engineblock.compat.corto_model_consent_factory');
    }

    /**
     * @return \OpenConext\EngineBlockBundle\AttributeAggregation\AttributeAggregationClientInterface
     */
    public function getAttributeAggregationClient()
    {
        return $this->container->get(\OpenConext\EngineBlockBundle\AttributeAggregation\AttributeAggregationClient::class);
    }

    /**
     * @return MetadataRepositoryInterface
     */
    public function getMetadataRepository()
    {
        return $this->container->get(\OpenConext\EngineBlock\Metadata\MetadataRepository\CachedDoctrineMetadataRepository::class);
    }

    /**
     * @return \OpenConext\EngineBlockBridge\Authentication\Repository\UserDirectoryAdapter
     */
    public function getUserDirectory()
    {
        return $this->container->get(\OpenConext\EngineBlockBridge\Authentication\Repository\UserDirectoryAdapter::class);
    }

    /**
     * @return OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration
     */
    public function getFeatureConfiguration()
    {
        return $this->container->get(\OpenConext\EngineBlockBundle\Configuration\FeatureConfiguration::class);
    }

    /**
     * @return OpenConext\EngineBlockBundle\Authentication\AuthenticationLoopGuard
     */
    public function getAuthenticationLoopGuard()
    {
        return $this->container->get(\OpenConext\EngineBlockBundle\Authentication\AuthenticationLoopGuard::class);
    }

    /**
     * @return OpenConext\EngineBlock\Service\ConsentService
     */
    public function getConsentService()
    {
        return $this->container->get(\OpenConext\EngineBlock\Service\ConsentService::class);
    }

    /**
     * @return TimeProviderInterface
     */
    public function getTimeProvider()
    {
        return $this->container->get(\OpenConext\EngineBlock\Service\TimeProvider\TimeProvider::class);
    }

    /**
     * @return EngineBlock_Saml2_IdGenerator
     */
    public function getSaml2IdGenerator()
    {
        return $this->container->get('engineblock.compat.saml2_id_generator');
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
            $definitionFile = $this->getAttributeDefinitionFilePath();
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

    public function shouldDisplayDefaultIdpBannerOnWayf()
    {
        return $this->container->getParameter('wayf.display_default_idp_banner_on_wayf');
    }

    public function getDefaultIdPEntityId()
    {
        if ($this->container->hasParameter('wayf.default_idp_entity_id')) {
            return $this->container->getParameter('wayf.default_idp_entity_id');
        }
        return null;
    }

    public function getRememberChoice()
    {
        return $this->container->getParameter('wayf.remember_choice');
    }

    /**
     * @return object|\Symfony\Component\HttpFoundation\Session\Session
     */
    public function getSession()
    {
        return $this->container->get('session');
    }

    public function getMailer(): MailerInterface
    {
        return $this->container->get('symfony.mailer');
    }

    /**
     * @return ReleaseAsEnforcer
     */
    public function getReleaseAsEnforcer()
    {
        return $this->container->get(ReleaseAsEnforcer::class);
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
        return $this->container->get(\OpenConext\EngineBlockBundle\Pdp\PdpClient::class);
    }

    public function getPdpClientId()
    {
        return $this->container->getParameter('pdp.client_id');
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
        return $this->container->get(\OpenConext\EngineBlockBundle\Localization\LocaleProvider::class);
    }

    /**
     * @return \Symfony\Contracts\Translation\TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->container->get('translator');
    }

    /**
     * @return \OpenConext\EngineBlockBundle\Url\UrlProvider
     */
    public function getUrlProvider()
    {
        return $this->container->get(\OpenConext\EngineBlockBundle\Url\UrlProvider::class);
    }

    /**
     * @param EngineBlock_Corto_ProxyServer $server
     * @return \OpenConext\EngineBlock\Metadata\Entity\IdentityProvider
     */
    public function getStepupIdentityProvider(EngineBlock_Corto_ProxyServer $server)
    {
        return StepupEntityFactory::idpFrom(
            $this->getStepupEndpoint(),
            $server->getUrl('stepupAssertionConsumerService')
        );
    }

    /**
     * @param EngineBlock_Corto_ProxyServer $server
     * @return \OpenConext\EngineBlock\Metadata\Entity\ServiceProvider
     */
    public function getStepupServiceProvider(EngineBlock_Corto_ProxyServer $server)
    {
        return StepupEntityFactory::spFrom(
            $this->getStepupEndpoint(),
            $server->getUrl('stepupAssertionConsumerService')
        );
    }

    /**
     * @return StepupGatewayCallOutHelper
     */
    public function getStepupGatewayCallOutHelper()
    {
        return $this->container->get(\OpenConext\EngineBlock\Stepup\StepupGatewayCallOutHelper::class);
    }


    /**
     * @return ServiceProviderFactory
     */
    public function getServiceProviderFactory()
    {
        return $this->container->get(\OpenConext\EngineBlock\Metadata\Factory\Factory\ServiceProviderFactory::class);
    }

    /**
     * @return LoaRepository
     */
    public function getLoaRepository()
    {
        return $this->container->get(\OpenConext\EngineBlock\Metadata\LoaRepository::class);
    }

    /**
     * @return array
     */
    public function getEncryptionKeysConfiguration()
    {
        return $this->container->getParameter('encryption_keys');
    }

    /**
     * @return Environment
     */
    public function getTwigEnvironment()
    {
        return $this->container->get('twig');
    }

    /**
     * @return array
     */
    public function getTrustedProxiesIpAddresses()
    {
        return (array) $this->container->getParameter('trusted_proxies');
    }

    /**
     * @return array
     */
    public function getForbiddenSignatureMethods()
    {
        return (array) $this->container->getParameter('forbidden_signature_methods');
    }

    /**
     * @return AllowedSchemeValidator
     */
    public function getAcsLocationSchemeValidator()
    {
        return $this->container->get(\OpenConext\EngineBlock\Validator\AllowedSchemeValidator::class);
    }

    /**
     * @return bool
     */
    public function isUiOptionReturnToSpActive()
    {
        return (bool) $this->container->getParameter('ui_return_to_sp_link');
    }

    /**
     * @return bool
     */
    public function isConsentStoreValuesActive()
    {
        return (bool) $this->container->getParameter('consent_store_values');
    }

    public function getAuthenticationStateHelper()
    {
        return $this->container->get(\OpenConext\EngineBlock\Service\AuthenticationStateHelper::class);
    }

    /**
     * @return \OpenConext\EngineBlock\Service\ProcessingStateHelperInterface
     */
    public function getProcessingStateHelper()
    {
        return $this->container->get(\OpenConext\EngineBlock\Service\ProcessingStateHelper::class);
    }

    /**
     * @return \OpenConext\EngineBlockBundle\Service\DiscoverySelectionService
     */
    public function getDiscoverySelectionService()
    {
        return $this->container->get(\OpenConext\EngineBlockBundle\Service\DiscoverySelectionService::class);
    }

    public function getMfaHelper(): MfaHelperInterface
    {
        return $this->container->get(\OpenConext\EngineBlock\Service\MfaHelper::class);
    }

    /**
     * @return string
     */
    public function getGuestStatusQualifier()
    {
        return (string) $this->container->getParameter('addgueststatus_guestqualifier');
    }

    /**
     * @return string
     */
    public function getCookiePath()
    {
        return (string) $this->container->getParameter('cookie.path');
    }

    /**
     * @return bool
     */
    public function getCookieUseSecure()
    {
        return (bool) $this->container->getParameter('cookie.secure');
    }

    /**
     * @return array
     */
    public function getEmailIdpDebuggingConfiguration()
    {
        return (array) $this->container->getParameter('email_idp_debugging');
    }

    /**
     * @return string
     */
    public function getProfileBaseUrl()
    {
        return (string) $this->container->getParameter('profile_base_url');
    }

    /**
     * @return string
     */
    public function getAttributeDefinitionFilePath()
    {
        return (string) $this->container->getParameter('attribute_definition_file_path');
    }

    /**
     * @return string
     */
    public function getAuthnContextClassRefBlacklistRegex()
    {
        return (string) $this->container->getParameter('stepup.authn_context_class_ref_blacklist_regex');
    }

    /** @return \OpenConext\EngineBlock\Stepup\StepupEndpoint $stepupEndpoint */
    protected function getStepupEndpoint()
    {
        return $this->container->get(\OpenConext\EngineBlock\Stepup\StepupEndpoint::class);
    }

    /** @return string */
    public function getStepupEntityIdOverrideValue()
    {
        return $this->container->getParameter('stepup.sfo.override_engine_entityid');
    }

    /** @return string[] */
    public function getStepupUserAttributes(): array
    {
        return $this->container->getParameter('stepup.callout_user_attributes');
    }

    public function getCookieDomain()
    {
        return $this->container->getParameter('cookie.locale.domain');
    }

    /**
     * @return OpenConext\EngineBlock\Service\CookieService
     */
    public function getCookieService()
    {
        return $this->container->get(\OpenConext\EngineBlock\Service\CookieService::class);
    }

    /**
     * @return OpenConext\EngineBlock\Service\SsoSessionService
     */
    public function getSsoSessionService()
    {
        return $this->container->get(\OpenConext\EngineBlock\Service\SsoSessionService::class);
    }

    /**
     * @return OpenConext\EngineBlock\Service\SsoNotificationService
     */
    public function getSsoNotificationService()
    {
        return $this->container->get(\OpenConext\EngineBlock\Service\SsoNotificationService::class);
    }

    /**
     * @return array
     */
    public function getAuthLogAttributes()
    {
        return $this->container->getParameter('auth.log.attributes');
    }

    /**
     * @return EngineBlock_Saml2_NameIdResolver
     */
    public function getNameIdResolver()
    {
        return new EngineBlock_Saml2_NameIdResolver($this->container->get('engineblock.compat.logger'));
    }

    /**
     * @return EngineBlock_Arp_NameIdSubstituteResolver
     */
    public function getNameIdSubstituteResolver()
    {
        return new EngineBlock_Arp_NameIdSubstituteResolver($this->container->get('engineblock.compat.logger'));
    }
}
