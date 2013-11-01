<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

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
     * @var mixed Callback called on Proxy server after configuration
     */
    protected $_remoteEntitiesFilter = array();

    public function singleSignOn($idPProviderHash)
    {
        $this->_addRemoteEntitiesFilter(array($this, '_filterRemoteEntitiesByRequestSp'));
        $this->_addRemoteEntitiesFilter(array($this, '_filterRemoteEntitiesByRequestSpWorkflowState'));
        $this->_addRemoteEntitiesFilter(array($this, '_filterRemoteEntitiesByRequestScopingRequesterId'));

        $this->_callCortoServiceUri('singleSignOnService', $idPProviderHash);
    }

    public function unsolicitedSingleSignOn($idPProviderHash)
    {
        $this->_addRemoteEntitiesFilter(array($this, '_filterRemoteEntitiesByClaimedSp'));
        $this->_addRemoteEntitiesFilter(array($this, '_filterRemoteEntitiesByClaimedSpWorkflowState'));

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
        $this->_addRemoteEntitiesFilter(array($this, '_filterRemoteEntitiesBySpQueryParam'));
        $this->_addRemoteEntitiesFilter(array($this, '_filterRemoteEntitiesByClaimedSpWorkflowState'));
        $this->_callCortoServiceUri('edugainMetadataService');
    }

    public function idPsMetadata()
    {
        $this->_addRemoteEntitiesFilter(array($this, '_filterRemoteEntitiesBySpQueryParam'));
        $this->_addRemoteEntitiesFilter(array($this, '_filterRemoteEntitiesByClaimedSpWorkflowState'));
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

    public function getVirtualOrganisationContext()
    {
        return $this->_voContext;
    }

    public function setVirtualOrganisationContext($virtualOrganisation)
    {
        $this->_voContext = $virtualOrganisation;
    }

    /**
     * Get the SAML2 Authn Request
     *
     * @return array $request
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
     *
     * @param array $entities
     * @return array Remaining entities
     */
    protected function _filterRemoteEntitiesByRequestSp(array $entities)
    {
        $issuerSpEntityId = $this->_getIssuerSpEntityId();
        $entityData = $this->_proxyServer->getRemoteEntity($issuerSpEntityId);

        if (isset($entityData['DisplayUnconnectedIdpsWayf']) && $entityData['DisplayUnconnectedIdpsWayf']) {
            return $this->getServiceRegistryAdapter()->markEntitiesBySp($entities, $issuerSpEntityId);
        }
        else {
            return $this->getServiceRegistryAdapter()->filterEntitiesBySp($entities, $issuerSpEntityId);
        }

    }

    /**
     * Filter out IdPs that are not allowed to connect to the given SP.
     *
     * Determines SP based on Authn Request (required).
     *
     * @param array $entities
     * @return array Remaining entities
     */
    protected function _filterRemoteEntitiesByClaimedSp(array $entities)
    {
        return $this->getServiceRegistryAdapter()->filterEntitiesBySp(
            $entities,
            $this->_getClaimedSpEntityId()
        );
    }

    protected function _filterRemoteEntitiesByRequestScopingRequesterId(array $entities)
    {
        $requesterIds = $this->_getRequestScopingRequesterIds();
        $serviceRegistry = $this->getServiceRegistryAdapter();
        foreach ($requesterIds as $requesterId) {
            if ($this->_proxyServer->hasRemoteEntity($requesterId)) {
                $entities = $serviceRegistry->filterEntitiesBySp($entities, $requesterId);
            }
            else {
                $this->_getSessionLog()->warn(
                    "Unable to apply RequesterID '$requesterId' to sub-scope the available IdPs as we don't know this SP!"
                );
            }
        }
        return $entities;
    }

    /**
     * Filter out IdPs that are not allowed to connect to the given SP.
     *
     * Determines SP based on URL query param (easily spoofable, thus 'claimed').
     *
     * @param array $entities
     * @return array Remaining entities
     */
    protected function _filterRemoteEntitiesBySpQueryParam(array $entities)
    {
        $claimedSpEntityId = $this->_getClaimedSpEntityId();
        if (!$claimedSpEntityId) {
            return $entities;
        }

        return $this->getServiceRegistryAdapter()->filterEntitiesBySp(
            $entities,
            $claimedSpEntityId
        );
    }

    /**
     * Given a list of Idps, filters out all that do not have the same state as the requesting SP.
     *
     * Determines SP based on Authn Request.
     *
     * @param array $entities
     * @return array Filtered entities
     */
    protected function _filterRemoteEntitiesByRequestSpWorkflowState(array $entities)
    {
        $spEntityId = $this->_getIssuerSpEntityId();
        return $this->getServiceRegistryAdapter()->filterEntitiesByWorkflowState(
            $entities,
            $this->_getEntityWorkFlowState($spEntityId)
        );
    }

    /**
     * Given a list of Idps, filters out all that do not have the same state as the claimed SP.
     *
     * Determines SP based on URL query param (easily spoofable, thus 'claimed').
     *
     * @param array $entities
     * @return array Filtered entities
     */
    protected function _filterRemoteEntitiesByClaimedSpWorkflowState(array $entities)
    {
        $claimedSpEntityId = $this->_getClaimedSpEntityId();
        if (!$claimedSpEntityId) {
            return $entities;
        }

        return $this->getServiceRegistryAdapter()->filterEntitiesByWorkflowState(
            $entities,
            $this->_getEntityWorkFlowState($claimedSpEntityId)
        );
    }

    /**
     * @return array RequesterIDs in Request Scoping (if any, otherwise empty)
     */
    protected function _getRequestScopingRequesterIds() {
        $request = $this->_getRequestInstance();
        $requesterIds = array();
        if (!empty($request['samlp:Scoping']['samlp:RequesterID'])) {
            foreach ($request['samlp:Scoping']['samlp:RequesterID'] as $requesterIdElement) {
                $requesterIds[] = $requesterIdElement['__v'];
            }
            return $requesterIds;
        }
        else {
            return $requesterIds;
        }
    }

    /**
     * @return string $issuerSpEntityId
     */
    protected function _getIssuerSpEntityId() {
        $request = $this->_getRequestInstance();
        $issuerSpEntityId = $request['saml:Issuer'][EngineBlock_Corto_XmlToArray::VALUE_PFX];
        return $issuerSpEntityId;
    }

    /**
     * @return $claimedSpEntityId
     */
    protected function _getClaimedSpEntityId() {
        $claimedSpEntityId = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest()->getQueryParameter('sp-entity-id');
        return $claimedSpEntityId;
    }

    /**
     * Gets workflow state for given entity id
     *
     * @param string $entityId
     * @return string $workflowState
     */
    protected function _getEntityWorkFlowState($entityId) {
        $entityData = $this->_proxyServer->getRemoteEntity($entityId);
        $workflowState = $entityData['WorkflowState'];
        return $workflowState;
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

        $this->_applyRemoteEntitiesFilters($this->_proxyServer);
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
            'NoSupportedIDPError' => 'user',
            'rememberIdp' => '+3 months',
            'SigningAlgorithm' => '', // @todo Look this up
            'certificates' => array(
                'public'    => $application->getConfiguration()->encryption->key->public,
                'private'   => $application->getConfiguration()->encryption->key->private,
            ),
            // Note that we use an input filter because consent requires a persistent NameID
            // and we only get that after provisioning
            'infilter'  => array(new EngineBlock_Corto_Filter_Input($this), 'filter'),
            'outfilter' => array(new EngineBlock_Corto_Filter_Output($this), 'filter'),
            'Processing' => array(
                'Consent' => array(
                    'Binding'  => 'INTERNAL',
                    'Location' => $proxyServer->getUrl('provideConsentService'),
                ),
            ),
            'metadataValidUntilSeconds' => 86400, // This sets the time (in seconds) the entity metadata is valid.
        ));

        $remoteEntities = $this->_getRemoteEntities();

        /**
         * Augment our own IdP entry with stuff that can't be set via the Service Registry (yet)
         */
        $idpEntityId = $proxyServer->getUrl('idpMetadataService');
        if (!isset($remoteEntities[$idpEntityId])) {
            $remoteEntities[$idpEntityId] = array();
        }
        $remoteEntities[$idpEntityId]['EntityID'] = $idpEntityId;
        $remoteEntities[$idpEntityId]['certificates'] = array(
            'public'    => $application->getConfiguration()->encryption->key->public,
            'private'   => $application->getConfiguration()->encryption->key->private,
        );
        $remoteEntities[$idpEntityId]['NameIDFormats'] = array(
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
        if (!isset($remoteEntities[$spEntityId])) {
            $remoteEntities[$spEntityId] = array();
        }
        $remoteEntities[$spEntityId]['EntityID'] = $spEntityId;
        $remoteEntities[$spEntityId]['certificates'] = array(
            'public'    => $application->getConfiguration()->encryption->key->public,
            'private'   => $application->getConfiguration()->encryption->key->private,
        );
        $remoteEntities[$spEntityId]['NameIDFormats'] = array(
            EngineBlock_Urn::SAML2_0_NAMEID_FORMAT_PERSISTENT,
            EngineBlock_Urn::SAML2_0_NAMEID_FORMAT_TRANSIENT,
            EngineBlock_Urn::SAML1_1_NAMEID_FORMAT_UNSPECIFIED,
            // @todo remove this as soon as it's no longer required to be supported for backwards compatibility
            EngineBlock_Urn::SAML2_0_NAMEID_FORMAT_UNSPECIFIED
        );
        $remoteEntities[$spEntityId]['RequestedAttributes'] = array(
            array(
                'Name' => 'urn:mace:dir:attribute-def:mail',
                'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri'
            ),

            // DisplayName (example: John Doe)
            array(
                'Name' => 'urn:mace:dir:attribute-def:displayName',
                'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri'
            ),

            // Surname (example: Doe)
            array(
                'Name' => 'urn:mace:dir:attribute-def:sn',
                'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri'
            ),

            // Given name (example: John)
            array(
                'Name' => 'urn:mace:dir:attribute-def:givenName',
                'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
            ),

            // SchachomeOrganization
            array(
                'Name' => 'urn:mace:terena.org:attribute-def:schacHomeOrganization',
                'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
                'Required' => true
            ),

            // SchachomeOrganizationType
            array(
                'Name' => 'urn:mace:terena.org:attribute-def:schacHomeOrganizationType',
                'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri'
            ),


            // UID (example: john.doe)
            array(
                'Name' => 'urn:mace:dir:attribute-def:uid',
                'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
                'Required' => true
            ),

            // Cn
            array(
                'Name' => 'urn:mace:dir:attribute-def:cn',
                'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri'
            ),

            // EduPersonAffiliation
            array(
                'Name' => 'urn:mace:dir:attribute-def:eduPersonAffiliation',
                'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri'
            ),

            // eduPersonEntitlement
            array(
                'Name' => 'urn:mace:dir:attribute-def:eduPersonEntitlement',
                'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri'
            ),

            // eduPersonPrincipalName
            array(
                'Name' => 'urn:mace:dir:attribute-def:eduPersonPrincipalName',
                'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri'
            ),

            // preferredLanguage
            array(
                'Name' => 'urn:mace:dir:attribute-def:preferredLanguage',
                'NameFormat' => 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri'
            )
        );

        // Store current entities separate from remote entities
        $proxyServer->setCurrentEntities(array(
            'spMetadataService' => $remoteEntities[$spEntityId],
            'idpMetadataService' => $remoteEntities[$idpEntityId],
        ));
        unset($remoteEntities[$spEntityId]);
        unset($remoteEntities[$idpEntityId]);
        $proxyServer->setRemoteEntities($remoteEntities);

        $proxyServer->setTemplateSource(
            EngineBlock_Corto_ProxyServer::TEMPLATE_SOURCE_FILESYSTEM,
            array('FilePath'=>ENGINEBLOCK_FOLDER_MODULES . 'Authentication/View/Proxy/')
        );

        $proxyServer->setBindingsModule(new EngineBlock_Corto_Module_Bindings($proxyServer));
        $proxyServer->setServicesModule(new EngineBlock_Corto_Module_Services($proxyServer));

        if ($this->_voContext!=null) {
            $proxyServer->setVirtualOrganisationContext($this->_voContext);
        }
    }

    /**
     * Applies remote entities filters and passes result to proxy server
     *
     * @return void
     */
    protected function _applyRemoteEntitiesFilters(EngineBlock_Corto_ProxyServer $proxyServer) {
        if (empty($this->_remoteEntitiesFilter)) {
            return;
        }

        $remoteEntities = $proxyServer->getRemoteEntities();

        foreach($this->_remoteEntitiesFilter as $remoteEntityFilter) {
            $remoteEntities = call_user_func_array(
                $remoteEntityFilter,
                array(
                    $remoteEntities,
                    $proxyServer
                )
            );
        }
        $proxyServer->setRemoteEntities($remoteEntities);
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

    protected function _getRemoteEntities()
    {
        $serviceRegistry = $this->getServiceRegistryAdapter();
        $metadata = $serviceRegistry->getRemoteMetaData();
        return $metadata;
    }

    public function getProxyServer()
    {
        return $this->_proxyServer;
    }

    public function getServiceRegistryAdapter()
    {
        return EngineBlock_ApplicationSingleton::getInstance()->getDiContainer()->getServiceRegistryAdapter();
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

    protected function _addRemoteEntitiesFilter($callback)
    {
        $this->_remoteEntitiesFilter[] = $callback;
        return $this;
    }

    protected function _getCoreProxy()
    {
        return new EngineBlock_Corto_ProxyServer();
    }
}
