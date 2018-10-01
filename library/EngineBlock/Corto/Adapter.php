<?php

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Filter\RemoveDisallowedIdentityProvidersFilter;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Filter\RemoveOtherWorkflowStatesFilter;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\DisableDisallowedEntitiesInWayfVisitor;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\EngineBlockMetadataVisitor;
use OpenConext\EngineBlock\Metadata\Service;

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

        $request = $this->_proxyServer
            ->getBindingsModule()
            ->receiveRequest();

        $this->_filterRemoteEntitiesByRequestSp($request);
        $this->_filterRemoteEntitiesByRequestSpWorkflowState($request);
        $this->_filterRemoteEntitiesByRequestScopingRequesterId($request);

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
        $this->_initProxy();

        $this->_filterRemoteEntitiesBySpQueryParam();
        $this->_filterRemoteEntitiesByClaimedSpWorkflowState();

        $this->_callCortoServiceUri('edugainMetadataService');
    }

    public function idPsMetadata()
    {
        $this->_initProxy();

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

    public function setKeyId($filter)
    {
        $this->_keyId = $filter;
    }

    /**
     * Filter out IdPs that are not allowed to connect to the given SP. We don't filter out
     * any IdP's if this is explicitly configured for the given in SR.
     *
     * Determines SP based on Authn Request (required).
     *
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     */
    protected function _filterRemoteEntitiesByRequestSp(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request)
    {
        $repository = $this->getMetadataRepository();
        $serviceProvider = $repository->fetchServiceProviderByEntityId($request->getIssuer());

        if (!$serviceProvider->allowAll) {
            if ($serviceProvider->displayUnconnectedIdpsWayf) {
                $repository->appendVisitor(
                    new DisableDisallowedEntitiesInWayfVisitor($serviceProvider->allowedIdpEntityIds)
                );
                return;
            }

            $repository->appendFilter(
                new RemoveDisallowedIdentityProvidersFilter(
                    $serviceProvider->entityId,
                    $this->_findAllowedIdpEntityIdsForSp($serviceProvider)
                )
            );
        }
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

        if (!$serviceProvider->allowAll) {
            $repository->appendFilter(
                new RemoveDisallowedIdentityProvidersFilter(
                    $serviceProvider->entityId,
                    $this->_findAllowedIdpEntityIdsForSp($serviceProvider)
                )
            );
        }
    }

    /**
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     */
    protected function _filterRemoteEntitiesByRequestScopingRequesterId(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request)
    {
        $requesterIds = $request->getRequesterID();

        $repository = $this->getMetadataRepository();
        foreach ($requesterIds as $requesterId) {
            $serviceProvider = $repository->findServiceProviderByEntityId($requesterId);

            if ($serviceProvider) {
                if (!$serviceProvider->allowAll) {
                    $repository->appendFilter(
                        new RemoveDisallowedIdentityProvidersFilter(
                            $serviceProvider->entityId,
                            $this->_findAllowedIdpEntityIdsForSp($serviceProvider)
                        )
                    );
                }
            }
            else {
                $this->_getLogger()->warning(
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

        if (!$serviceProvider->allowAll) {
            $this->getMetadataRepository()->appendFilter(
                new RemoveDisallowedIdentityProvidersFilter(
                    $serviceProvider->entityId,
                    $this->_findAllowedIdpEntityIdsForSp($serviceProvider)
                )
            );
        }
    }

    /**
     * Given a list of Idps, filters out all that do not have the same state as the requesting SP.
     *
     * Determines SP based on Authn Request.
     *
     * @param EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request
     */
    protected function _filterRemoteEntitiesByRequestSpWorkflowState(EngineBlock_Saml2_AuthnRequestAnnotationDecorator $request)
    {
        $repository = $this->getMetadataRepository();
        $serviceProvider = $repository->fetchServiceProviderByEntityId($request->getIssuer());

        $filter = new RemoveOtherWorkflowStatesFilter(
            $serviceProvider,
            $this->getProxyServer()->getUrl('idpMetadataService'),
            $this->getProxyServer()->getUrl('spMetadataService')
        );

        $repository->appendFilter($filter);
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

        $filter = new RemoveOtherWorkflowStatesFilter(
            $serviceProvider,
            $this->getProxyServer()->getUrl('idpMetadataService'),
            $this->getProxyServer()->getUrl('spMetadataService')
        );

        $repository->appendFilter($filter);
    }

    /**
     * @param ServiceProvider $serviceProvider
     * @return array|string[]
     */
    private function _findAllowedIdpEntityIdsForSp(ServiceProvider $serviceProvider)
    {
        $entityIds = $serviceProvider->allowedIdpEntityIds;
        $entityIds[] = $this->_proxyServer->getUrl('idpMetadataService');
        return $entityIds;
    }

    /**
     * @return $claimedSpEntityId
     */
    protected function _getClaimedSpEntityId()
    {
        $claimedSpEntityId = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest()->getQueryParameter('sp-entity-id');
        return $claimedSpEntityId;
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
        $proxyServer->startSession();

        $this->_configureProxyServer($proxyServer);

        $this->_proxyServer = $proxyServer;
    }

    protected function _configureProxyServer(EngineBlock_Corto_ProxyServer $proxyServer)
    {
        $proxyServer->setLogger($this->_getLogger());

        $application = EngineBlock_ApplicationSingleton::getInstance();
        $settings = $application->getDiContainer();

        $proxyServer->setHostName($settings->getHostname());

        $proxyServer->setConfigs(array(
            'debug' => $settings->isDebug(),
            'ConsentStoreValues' => $settings->isConsentStoreValuesActive(),
            'metadataValidUntilSeconds' => 86400, // This sets the time (in seconds) the entity metadata is valid.
            'forbiddenSignatureMethods' => $settings->getForbiddenSignatureMethods(),
        ));

        $this->configureProxyCertificates($proxyServer);

        $this->enrichEngineBlockMetadata($proxyServer);

        $proxyServer->setRepository($this->getMetadataRepository());
        $proxyServer->setConfig('Processing', ['Consent' => $this->getEngineSpRole($proxyServer)]);
        $proxyServer->setBindingsModule(new EngineBlock_Corto_Module_Bindings($proxyServer));
        $proxyServer->setServicesModule(new EngineBlock_Corto_Module_Services($proxyServer));
    }

    /**
     * @return Psr\Log\LoggerInterface
     */
    protected function _getLogger()
    {
        return EngineBlock_ApplicationSingleton::getLog();
    }

    public function getProxyServer()
    {
        return $this->_proxyServer;
    }

    /**
     * @return MetadataRepositoryInterface
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
        $twig = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getTwigEnvironment();
        return new EngineBlock_Corto_ProxyServer($twig);
    }

    /**
     * Get all certificates from the configuration, the certificate key we were configured with and tell them to
     * the proxy server. Let the proxy server then decide which signing certificates to use.
     *
     * @param EngineBlock_Corto_ProxyServer $proxyServer
     * @return EngineBlock_X509_KeyPair
     * @throws EngineBlock_Corto_ProxyServer_Exception
     * @throws EngineBlock_Exception
     */
    protected function configureProxyCertificates(EngineBlock_Corto_ProxyServer $proxyServer)
    {
        $keysConfig = EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getEncryptionKeysConfiguration();

        if (empty($keysConfig)) {
            throw new EngineBlock_Corto_ProxyServer_Exception("No encryption/signing keys defined!");
        }

        $publicKeyFactory = new EngineBlock_X509_CertificateFactory();
        $keyPairs = array();
        foreach ($keysConfig as $keyId => $keyConfig) {
            if (!isset($keyConfig['privateFile'])) {
                $this->_getLogger()->warning(
                    'Reference to private key file not found for key: ' . $keyId . ' skipping keypair.'
                );
                continue;
            }
            if (!isset($keyConfig['publicFile'])) {
                $this->_getLogger()->warning('Reference to public key file not found for key: ' . $keyId);
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
     * Modify EngineBlocks own metadata entries.
     *
     * See EngineBlockMetadataVisitor for more information on what is modified
     * and why.
     *
     * @param EngineBlock_Corto_ProxyServer $proxyServer
     */
    protected function enrichEngineBlockMetadata(EngineBlock_Corto_ProxyServer $proxyServer)
    {
        $idpEntityId = $proxyServer->getUrl('idpMetadataService');
        $spEntityId = $proxyServer->getUrl('spMetadataService');
        $keyPair = $proxyServer->getSigningCertificates();

        $visitor = new EngineBlockMetadataVisitor(
            $idpEntityId,
            $spEntityId,
            $keyPair,
            EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getAttributeMetadata(),
            new Service(
                $proxyServer->getUrl('provideConsentService'),
                'INTERNAL'
            )
        );

        $this->getMetadataRepository()->appendVisitor($visitor);
    }

    /**
     * @param EngineBlock_Corto_ProxyServer $proxyServer
     * @return ServiceProvider
     * @throws EngineBlock_Corto_ProxyServer_Exception
     * @throws EngineBlock_Exception
     */
    protected function getEngineSpRole(EngineBlock_Corto_ProxyServer $proxyServer)
    {
        $spEntityId = $proxyServer->getUrl('spMetadataService');
        $engineServiceProvider = $proxyServer->getRepository()->findServiceProviderByEntityId($spEntityId);
        if (!$engineServiceProvider) {
            throw new EngineBlock_Exception(
                "Unable to find EngineBlock configured as Service Provider. No '$spEntityId' in repository!"
            );
        }

        return $engineServiceProvider;
    }
}
