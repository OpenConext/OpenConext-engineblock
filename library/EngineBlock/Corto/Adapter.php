<?php

use OpenConext\Component\EngineBlockMetadata\Entity\MetadataRepository\AggregatedMetadataRepository;
use OpenConext\Component\EngineBlockMetadata\RequestedAttribute;
use OpenConext\Component\EngineBlockMetadata\Entity\MetadataRepository\Filter\DisableDisallowedEntitiesInWayfFilter;
use OpenConext\Component\EngineBlockMetadata\Entity\MetadataRepository\Filter\RemoveDisallowedIdentityProvidersFilter;
use OpenConext\Component\EngineBlockMetadata\Entity\MetadataRepository\Filter\RemoveEntityByEntityId;
use OpenConext\Component\EngineBlockMetadata\Entity\MetadataRepository\Filter\RemoveOtherWorkflowStatesFilter;
use OpenConext\Component\EngineBlockMetadata\Entity\MetadataRepository\InMemoryMetadataRepository;
use OpenConext\Component\EngineBlockMetadata\Service;

class EngineBlock_Corto_Adapter
{
    const DEFAULT_HOSTED_ENTITY = 'main';

    const IDENTIFYING_MACE_ATTRIBUTE = 'urn:mace:dir:attribute-def:uid';

    protected $_collaborationAttributes = array();

    /**
     * @var EngineBlock_Corto_ProxyServer
     */
    protected $_proxyServer;

    /**
     * @var String the name of the Virtual Organisation context (if any)
     */
    protected $_voContext = NULL;

    /**
     * @var null
     */
    protected $_keyId = NULL;

    /**
     * @var mixed Callback called on Proxy server after configuration
     */
    protected $_remoteEntitiesFilter = array();

    public function singleSignOn($idPProviderHash)
    {
        $this->_initProxy();

        $this->_filterRemoteEntitiesByRequestSp();
        $this->_filterRemoteEntitiesByRequestSpWorkflowState();
        $this->_filterRemoteEntitiesByRequestScopingRequesterId();

        $this->_callCortoServiceUri('singleSignOnService', $idPProviderHash);
    }

    public function unsolicitedSingleSignOn($idPProviderHash)
    {
        $this->_initProxy();

        $this->_filterRemoteEntitiesByClaimedSp();
        $this->_filterRemoteEntitiesByClaimedSpWorkflowState();

        $this->_callCortoServiceUri('unsolicitedSingleSignOnService', $idPProviderHash);
    }

    public function debugSingleSignOn()
    {
        $this->_callCortoServiceUri('debugSingleSignOnService');
    }

    public function idPMetadata()
    {
        $this->_callCortoServiceUri('idpMetadataService');
    }

    public function idpCertificate()
    {
        $this->_callCortoServiceUri('idpCertificateService');
    }

    public function sPMetadata()
    {
        $this->_callCortoServiceUri('spMetadataService');
    }

    public function spCertificate()
    {
        $this->_callCortoServiceUri('idpCertificateService');
    }

    public function consumeAssertion()
    {
        $this->_callCortoServiceUri('assertionConsumerService');
    }

    public function edugainMetadata()
    {
        $this->_filterRemoteEntitiesBySpQueryParam();
        $this->_filterRemoteEntitiesByClaimedSpWorkflowState();
        $this->_callCortoServiceUri('edugainMetadataService');
    }

    public function idPsMetadata()
    {
        $this->_filterRemoteEntitiesBySpQueryParam();
        $this->_filterRemoteEntitiesByClaimedSpWorkflowState();
        $this->_callCortoServiceUri('idpsMetadataService');
    }

    public function processWayf()
    {
        $this->_callCortoServiceUri('continueToIdp');
    }

    public function processConsent()
    {
        $this->_callCortoServiceUri('processConsentService');
    }

    public function processedAssertionConsumer()
    {
        $this->_callCortoServiceUri('processedAssertionConsumerService');
    }

    public function setVirtualOrganisationContext($virtualOrganisation)
    {
        $this->_voContext = $virtualOrganisation;
    }

    public function setKeyId($filter)
    {
        $this->_keyId = $filter;
    }

    /**
     * Get the SAML2 Authn Request
     *
     * @return EngineBlock_Saml2_AuthnRequestAnnotationDecorator
     */
    protected function _getRequestInstance() {
        // Use the binding module to get the request
        $bindingModule = $this->_proxyServer->getBindingsModule();
        $request = $bindingModule->receiveRequest();
        // then store it back so Corto will think it has received it
        // from an internal binding, because if Corto would try to
        // get the request again from the binding module, it would fail.
        $bindingModule->registerInternalBindingMessage('SAMLRequest', $request);
        return $request;
    }

    /**
     * Filter out IdPs that are not allowed to connect to the given SP. We don't filter out
     * any IdP's if this is explicitly configured for the given in SR.
     *
     * Determines SP based on Authn Request (required).
     */
    protected function _filterRemoteEntitiesByRequestSp()
    {
        $repository = $this->getMetadataRepository();
        $serviceProvider = $repository->fetchServiceProviderByEntityId($this->_getIssuerSpEntityId());

        if ($serviceProvider->displayUnconnectedIdpsWayf) {
            $repository->filter(new DisableDisallowedEntitiesInWayfFilter(
                $serviceProvider->entityId,
                $repository->findAllowedIdpEntityIdsForSp($serviceProvider)
            ));
            return;
        }

        $repository->filter(
            new RemoveDisallowedIdentityProvidersFilter(
                $serviceProvider->entityId,
                $repository->findAllowedIdpEntityIdsForSp($serviceProvider)
            )
        );
    }

    /**
     * Filter out IdPs that are not allowed to connect to the given SP.
     *
     * Determines SP based on Authn Request (required).
     */
    protected function _filterRemoteEntitiesByClaimedSp()
    {
        $serviceProviderEntityId = $this->_getClaimedSpEntityId();
        if (!$serviceProviderEntityId) {
            return;
        }

        $repository = $this->getMetadataRepository();
        $serviceProvider = $repository->fetchServiceProviderByEntityId($serviceProviderEntityId);
        $repository->filter(
            new RemoveDisallowedIdentityProvidersFilter(
                $serviceProvider->entityId,
                $repository->findAllowedIdpEntityIdsForSp($serviceProvider)
            )
        );
    }

    protected function _filterRemoteEntitiesByRequestScopingRequesterId()
    {
        $requesterIds = $this->_getRequestScopingRequesterIds();

        $repository = $this->getMetadataRepository();
        foreach ($requesterIds as $requesterId) {
            $serviceProvider = $repository->findServiceProviderByEntityId($requesterId);

            if ($serviceProvider) {
                $repository->filter(
                    new RemoveDisallowedIdentityProvidersFilter(
                        $serviceProvider->entityId,
                        $repository->findAllowedIdpEntityIdsForSp($serviceProvider)
                    )
                );
            }
            else {
                $this->_getSessionLog()->warn(
                    "Unable to apply RequesterID '$requesterId' to sub-scope the available IdPs as we don't know this SP!"
                );
            }
        }
    }

    /**
     * Filter out IdPs that are not allowed to connect to the given SP.
     *
     * Determines SP based on URL query param (easily spoofable, thus 'claimed').
     */
    protected function _filterRemoteEntitiesBySpQueryParam()
    {
        $claimedSpEntityId = $this->_getClaimedSpEntityId();
        if (!$claimedSpEntityId) {
            return;
        }

        $repository = $this->getMetadataRepository();
        $serviceProvider = $repository->findServiceProviderByEntityId($claimedSpEntityId);
        if (!$serviceProvider) {
            return;
        }

        $this->getMetadataRepository()->filter(
            new RemoveDisallowedIdentityProvidersFilter(
                $serviceProvider->entityId,
                $repository->findAllowedIdpEntityIdsForSp($serviceProvider)
            )
        );
    }

    /**
     * Given a list of Idps, filters out all that do not have the same state as the requesting SP.
     *
     * Determines SP based on Authn Request.
     */
    protected function _filterRemoteEntitiesByRequestSpWorkflowState()
    {
        $spEntityId = $this->_getIssuerSpEntityId();

        $repository = $this->getMetadataRepository();
        $serviceProvider = $repository->fetchServiceProviderByEntityId($spEntityId);

        $repository->filter(new RemoveOtherWorkflowStatesFilter($serviceProvider));
    }

    /**
     * Given a list of Idps, filters out all that do not have the same state as the claimed SP.
     *
     * Determines SP based on URL query param (easily spoofable, thus 'claimed').
     */
    protected function _filterRemoteEntitiesByClaimedSpWorkflowState()
    {
        $claimedSpEntityId = $this->_getClaimedSpEntityId();
        if (!$claimedSpEntityId) {
            return;
        }

        $repository = $this->getMetadataRepository();
        $serviceProvider = $repository->findServiceProviderByEntityId($claimedSpEntityId);
        if (!$serviceProvider) {
            return;
        }

        $repository->filter(new RemoveOtherWorkflowStatesFilter($serviceProvider));
    }

    /**
     * @return array RequesterIDs in Request Scoping (if any, otherwise empty)
     */
    protected function _getRequestScopingRequesterIds()
    {
        $request = $this->_getRequestInstance();
        /** @var SAML2_AuthnRequest $request */
        return $request->getRequesterID();
    }

    /**
     * @return string $issuerSpEntityId
     */
    protected function _getIssuerSpEntityId()
    {
        return $this->_getRequestInstance()->getIssuer();
    }

    /**
     * @return $claimedSpEntityId
     */
    protected function _getClaimedSpEntityId()
    {
        $claimedSpEntityId = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest()->getQueryParameter('sp-entity-id');
        return $claimedSpEntityId;
    }

    /**
     * Gets workflow state for given entity id
     *
     * @param string $entityId
     * @return string $workflowState
     */
    protected function _getEntityWorkFlowState($entityId)
    {
        return $this->_proxyServer->getRepository()->fetchEntityByEntityId($entityId)->workflowState;
    }

    protected function _callCortoServiceUri($serviceName, $idPProviderHash = "")
    {
        $this->_initProxy();

        $this->_proxyServer->serve($serviceName, $idPProviderHash);

        $this->_processProxyServerResponse();

        unset($this->_proxyServer);
    }

    protected function _initProxy()
    {
        if (isset($this->_proxyServer)) {
            return;
        }

        $proxyServer = $this->_getCoreProxy();

        $this->_configureProxyServer($proxyServer);

        $this->_proxyServer = $proxyServer;
    }

    protected function _configureProxyServer(EngineBlock_Corto_ProxyServer $proxyServer)
    {
        $proxyServer->setSystemLog($this->_getSystemLog());
        $proxyServer->setSessionLogDefault($this->_getSessionLog());

        $application = EngineBlock_ApplicationSingleton::getInstance();

        $proxyServer->setConfigs(array(
            'debug' => $application->getConfigurationValue('debug', false),
            'trace' => $application->getConfigurationValue('debug', false),
            'ConsentStoreValues' => $this->_getConsentConfigurationValue('storeValues', true),
            'rememberIdp' => '+3 months',
            'SigningAlgorithm' => '',
            'metadataValidUntilSeconds' => 86400, // This sets the time (in seconds) the entity metadata is valid.
        ));

        /**
         * Augment our own IdP entry with stuff that can't be set via the Service Registry (yet)
         */
        $metadataRepository = $this->_configureMetadataRepository($proxyServer, $application);

        $proxyServer->setRepository($metadataRepository);

        $proxyServer->setBindingsModule(new EngineBlock_Corto_Module_Bindings($proxyServer));
        $proxyServer->setServicesModule(new EngineBlock_Corto_Module_Services($proxyServer));

        if ($this->_voContext!=null) {
            $proxyServer->setVirtualOrganisationContext($this->_voContext);
        }
    }

    /**
     * @return EngineBlock_Log
     */
    protected function _getSystemLog()
    {
        return EngineBlock_ApplicationSingleton::getLog();
    }

    /**
     * @return EngineBlock_Log
     */
    protected function _getSessionLog()
    {
        return EngineBlock_ApplicationSingleton::getLog();
    }

    protected function _getConsentConfigurationValue($name, $default = null)
    {
        $configuration = EngineBlock_ApplicationSingleton::getInstance()->getConfiguration();
        if (!isset($configuration->authentication)) {
            return $default;
        }
        if (!isset($configuration->authentication->consent)) {
            return $default;
        }
        if (!isset($configuration->authentication->consent->$name)) {
            return $default;
        }
        return $configuration->authentication->consent->$name;
    }

    public function getProxyServer()
    {
        return $this->_proxyServer;
    }

    /**
     * @return AggregatedMetadataRepository
     */
    public function getMetadataRepository()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getMetadataRepository();
    }

    public function getDateTime()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getTimeProvider();
    }

    protected function _processProxyServerResponse()
    {
        $response = EngineBlock_ApplicationSingleton::getInstance()->getHttpResponse();

        $this->_processProxyServerResponseHeaders($response);
        $this->_processProxyServerResponseBody($response);
    }

    protected function _processProxyServerResponseHeaders(EngineBlock_Http_Response $response)
    {
        $proxyHeaders = $this->_proxyServer->getHeaders();
        foreach ($proxyHeaders as $headerName => $headerValue) {
            if ($headerName === EngineBlock_Http_Response::HTTP_HEADER_RESPONSE_LOCATION) {
                $response->setRedirectUrl($headerValue);
            }
            else {
                $response->setHeader($headerName, $headerValue);
            }
        }
    }

    protected function _processProxyServerResponseBody(EngineBlock_Http_Response $response)
    {
        $proxyOutput = $this->_proxyServer->getOutput();
        $response->setBody($proxyOutput);
    }

    protected function _getCoreProxy()
    {
        return new EngineBlock_Corto_ProxyServer();
    }

    /**
     * Get all certificates from the configuration, the certificate key we were configured with and tell them to
     * the proxy server. Let the proxy server then decide which signing certificates to use.
     *
     * @param EngineBlock_Corto_ProxyServer $proxyServer
     * @param Zend_Config $applicationConfiguration
     * @return EngineBlock_X509_KeyPair
     * @throws EngineBlock_Corto_ProxyServer_Exception
     * @throws EngineBlock_Exception
     */
    protected function configureProxyCertificates(
        EngineBlock_Corto_ProxyServer $proxyServer,
        Zend_Config $applicationConfiguration)
    {
        if (!isset($applicationConfiguration->encryption) || !isset($applicationConfiguration->encryption->keys)) {
            throw new EngineBlock_Corto_ProxyServer_Exception("No encryption/signing keys defined!");
        }

        $keysConfig = $applicationConfiguration->encryption->keys->toArray();

        if (empty($keysConfig)) {
            throw new EngineBlock_Corto_ProxyServer_Exception("No encryption/signing keys defined!");
        }

        $publicKeyFactory = new EngineBlock_X509_CertificateFactory();
        $keyPairs = array();
        foreach ($keysConfig as $keyId => $keyConfig) {
            if (!isset($keyConfig['privateFile'])) {
                $this->_getSessionLog()->log(
                    'Reference to private key file not found for key: ' . $keyId . ' skipping keypair.',
                    Zend_Log::WARN
                );
                continue;
            }
            if (!isset($keyConfig['publicFile'])) {
                $this->_getSessionLog()->log(
                    'Reference to public key file not found for key: ' . $keyId,
                    Zend_Log::WARN
                );
                continue;
            }

            $keyPairs[$keyId] = new EngineBlock_X509_KeyPair(
                $publicKeyFactory->fromFile($keyConfig['publicFile']),
                new EngineBlock_X509_PrivateKey($keyConfig['privateFile'])
            );
        }

        if (empty($keyPairs)) {
            throw new EngineBlock_Exception(
                'No (valid) keypairs found in configuration! Please configure at least 1 keypair under encryption.keys'
            );
        }

        $proxyServer->setKeyPairs($keyPairs);

        if ($this->_keyId !== null) {
            $proxyServer->setKeyId($this->_keyId);
        }

        return $proxyServer->getSigningCertificates();
    }

    /**
     * @param EngineBlock_Corto_ProxyServer $proxyServer
     * @param EngineBlock_ApplicationSingleton $application
     * @return AggregatedMetadataRepository
     * @throws EngineBlock_Exception
     */
    protected function _configureMetadataRepository(
        EngineBlock_Corto_ProxyServer $proxyServer,
        EngineBlock_ApplicationSingleton $application
    ) {
        $idpEntityId = $proxyServer->getUrl('idpMetadataService');
        $metadataRepository = $this->getMetadataRepository();
        $engineIdentityProvider = $metadataRepository->findIdentityProviderByEntityId($idpEntityId);
        if (!$engineIdentityProvider) {
            throw new EngineBlock_Exception(
                "Unable to find EngineBlock configured as Identity Provider. No '$idpEntityId' in repository!"
            );
        }

        $keyPair = $this->configureProxyCertificates($proxyServer, $application->getConfiguration());

        $engineIdentityProvider->certificates = array($keyPair->getCertificate());
        $engineIdentityProvider->nameIdFormats = array(
            EngineBlock_Urn::SAML2_0_NAMEID_FORMAT_PERSISTENT,
            EngineBlock_Urn::SAML2_0_NAMEID_FORMAT_TRANSIENT,
            EngineBlock_Urn::SAML1_1_NAMEID_FORMAT_UNSPECIFIED,
            // @todo remove this as soon as it's no longer required to be supported for backwards compatibility
            EngineBlock_Urn::SAML2_0_NAMEID_FORMAT_UNSPECIFIED
        );

        /**
         * Augment our own SP entry with stuff that can't be set via the Service Registry (yet)
         */
        $spEntityId = $proxyServer->getUrl('spMetadataService');
        $engineServiceProvider = $metadataRepository->findServiceProviderByEntityId($spEntityId);
        if (!$engineServiceProvider) {
            throw new EngineBlock_Exception(
                "Unable to find EngineBlock configured as Service Provider. No '$spEntityId' in repository!"
            );
        }
        $engineServiceProvider->certificates = array($keyPair->getCertificate());
        $engineServiceProvider->nameIdFormats = array(
            EngineBlock_Urn::SAML2_0_NAMEID_FORMAT_PERSISTENT,
            EngineBlock_Urn::SAML2_0_NAMEID_FORMAT_TRANSIENT,
            EngineBlock_Urn::SAML1_1_NAMEID_FORMAT_UNSPECIFIED,
            // @todo remove this as soon as it's no longer required to be supported for backwards compatibility
            EngineBlock_Urn::SAML2_0_NAMEID_FORMAT_UNSPECIFIED
        );
        $engineServiceProvider->requestedAttributes = array(
            new RequestedAttribute('urn:mace:dir:attribute-def:mail'),
            new RequestedAttribute('urn:mace:dir:attribute-def:displayName'), // DisplayName (example: John Doe)
            new RequestedAttribute('urn:mace:dir:attribute-def:sn'), // Surname (example: Doe)
            new RequestedAttribute('urn:mace:dir:attribute-def:givenName'), // Given name (example: John)
            new RequestedAttribute('urn:mace:terena.org:attribute-def:schacHomeOrganization', true),
            new RequestedAttribute('urn:mace:terena.org:attribute-def:schacHomeOrganizationType', true),
            new RequestedAttribute('urn:mace:dir:attribute-def:uid', true), // UID (example: john.doe)
            new RequestedAttribute('urn:mace:dir:attribute-def:cn'),
            new RequestedAttribute('urn:mace:dir:attribute-def:eduPersonAffiliation'),
            new RequestedAttribute('urn:mace:dir:attribute-def:eduPersonEntitlement'),
            new RequestedAttribute('urn:mace:dir:attribute-def:eduPersonPrincipalName'),
            new RequestedAttribute('urn:mace:dir:attribute-def:preferredLanguage'),
        );
        $engineServiceProvider->responseProcessingService = new Service(
            $proxyServer->getUrl('provideConsentService'),
            'INTERNAL'
        );
        $proxyServer->setConfig('Processing', array('Consent' => $engineServiceProvider));

        $metadataRepository->filter(new RemoveEntityByEntityId($engineServiceProvider->entityId));
        $metadataRepository->filter(new RemoveEntityByEntityId($engineIdentityProvider->entityId));

        $ownMetadataRepository = new InMemoryMetadataRepository(
            array($engineIdentityProvider),
            array($engineServiceProvider)
        );
        $metadataRepository->appendRepository($ownMetadataRepository);
        return $metadataRepository;
    }
}
