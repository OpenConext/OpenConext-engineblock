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

use OpenConext\EngineBlock\Metadata\Entity\ServiceProvider;
use OpenConext\EngineBlock\Metadata\MetadataRepository\EntityNotFoundException;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Filter\RemoveDisallowedIdentityProvidersFilter;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Filter\RemoveOtherWorkflowStatesFilter;
use OpenConext\EngineBlock\Metadata\MetadataRepository\MetadataRepositoryInterface;
use OpenConext\EngineBlock\Metadata\MetadataRepository\Visitor\DisableDisallowedEntitiesInWayfVisitor;
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

    public function stepupMetadata()
    {
        $this->_callCortoServiceUri('stepupMetadataService');
    }

    public function spCertificate()
    {
        $this->_callCortoServiceUri('idpCertificateService');
    }

    public function consumeAssertion()
    {
        $this->_callCortoServiceUri('assertionConsumerService');
    }

    public function stepupConsumeAssertion()
    {
        $this->_callCortoServiceUri('stepupAssertionConsumerService');
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
        $serviceProvider = $repository->fetchServiceProviderByEntityId($request->getIssuer()->getValue());

        if (!$serviceProvider->allowAll) {
            if ($serviceProvider->getCoins()->displayUnconnectedIdpsWayf()) {
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
     *
     * @throws EngineBlock_Exception_UnknownServiceProvider
     */
    protected function _filterRemoteEntitiesByClaimedSp()
    {
        $serviceProviderEntityId = $this->_getClaimedSpEntityId();
        if (!$serviceProviderEntityId) {
            return;
        }

        $repository = $this->getMetadataRepository();
        try {
            $serviceProvider = $repository->fetchServiceProviderByEntityId($serviceProviderEntityId);
        } catch (EntityNotFoundException $e) {
            throw new EngineBlock_Exception_UnknownServiceProvider(
                sprintf('Unable to find the claimed SP with entity ID "%s".', $serviceProviderEntityId),
                $serviceProviderEntityId
            );
        }

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
                $this->_getLogger()->info(
                    "SP passes RequesterID '$requesterId', using it to sub-scope the available IdPs"
                );
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
                $this->_getLogger()->info(
                    "SP passes RequesterID '$requesterId' which is unknown to us, ignoring"
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
        $serviceProvider = $repository->fetchServiceProviderByEntityId($request->getIssuer()->getValue());

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

        $proxyServer->setConfigs(array(
            'ConsentStoreValues' => $settings->isConsentStoreValuesActive(),
            'metadataValidUntilSeconds' => 86400, // This sets the time (in seconds) the entity metadata is valid.
            'forbiddenSignatureMethods' => $settings->getForbiddenSignatureMethods()
        ));

        $this->configureProxyCertificates($proxyServer);

        $proxyServer->setRepository($this->getMetadataRepository());
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
}
