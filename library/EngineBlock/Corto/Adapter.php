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
     * @var EngineBlock_Corto_CoreProxy
     */
    protected $_proxyServer;
    
    /**
     * @var String The name of the currently hosted Corto hosted entity.
     */
    protected $_hostedEntity;
    
    /**
     * @var String the name of the Virtual Organisation context (if any)
     */
    protected $_voContext = NULL;

    /**
     * @var mixed Callback called on Proxy server after configuration
     */
    protected $_remoteEntitiesFilter = array();
    
    public function __construct($hostedEntity = NULL)
    {
        if ($hostedEntity == NULL) {
            $hostedEntity = self::DEFAULT_HOSTED_ENTITY;
        }

        $this->_hostedEntity = $hostedEntity;
    }

    public function singleSignOn($idPProviderHash)
    {
        $this->_addRemoteEntitiesFilter(array($this, '_filterRemoteEntitiesByRequestSp'));
        $this->_callCortoServiceUri('singleSignOnService', $idPProviderHash);
    }

    public function idPMetadata()
    {
        $this->_callCortoServiceUri('idPMetadataService');
    }

    public function sPMetadata()
    {
        $this->_callCortoServiceUri('sPMetadataService');
    }

    public function consumeAssertion()
    {
        $this->_callCortoServiceUri('assertionConsumerService');
    }

    public function idPsMetadata()
    {
        $this->_addRemoteEntitiesFilter(array($this, '_filterRemoteEntitiesBySpQueryParam'));
        $this->_callCortoServiceUri('idPsMetaDataService');
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
     * Use the binding module to get the request, then
     * store it in _REQUEST so Corto will think it has received it
     * from an internal binding, because if Corto would try to
     * get the request again from the binding module, it would fail.
     *
     * @return array $request
     */
    protected function _getRequestInstance() {
        static $request;
        if(empty($request)) {

            $request = $this->_proxyServer->getBindingsModule()->receiveRequest();
            $_REQUEST['SAMLRequest'] = $request;
        }
        return $request;
    }

    protected function _filteRemoteEntitiesByRequestSp(array $entities, EngineBlock_Corto_CoreProxy $proxyServer)
    {
        $request = $this->_getRequestInstance();
        $spEntityId = $request['saml:Issuer']['__v'];

        return $this->getServiceRegistryAdapter()->filterEntitiesBySp(
            $entities,
            $spEntityId
        );
    }

    protected function _filterRemoteEntitiesBySpQueryParam(array $entities, EngineBlock_Corto_CoreProxy $proxyServer)
    {
        $claimedSpEntityId = EngineBlock_ApplicationSingleton::getInstance()->getHttpRequest()->getQueryParameter('sp-entity-id');
        if (!$claimedSpEntityId) {
            return $entities;
        }

        return $this->getServiceRegistryAdapter()->filterEntitiesBySp(
            $entities,
            $claimedSpEntityId
        );
    }

    protected function _callCortoServiceUri($serviceName, $idPProviderHash = "")
    {
        $this->_initProxy();

        $cortoUri = $this->_getCortoUri($serviceName, $idPProviderHash);
        $this->_proxyServer->serveRequest($cortoUri);
        $this->_processProxyServerResponse();

        unset($this->_proxyServer);
    }

    protected function _getCortoUri($cortoServiceName, $idPProviderHash = "")
    {
        $cortoHostedEntity  = $this->_getHostedEntity();
        $cortoIdPHash       = $idPProviderHash;
        $result =  '/' . $cortoHostedEntity . ($cortoIdPHash ? '_' . $cortoIdPHash : '') . '/' . $cortoServiceName;
        
        return $result;
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

    protected function _configureProxyServer(EngineBlock_Corto_CoreProxy $proxyServer)
    {
        $proxyServer->setSystemLog($this->_getSystemLog());
        $proxyServer->setSessionLogDefault($this->_getSessionLog());

        $application = EngineBlock_ApplicationSingleton::getInstance();

        $proxyServer->setConfigs(array(
            'debug' => $application->getConfigurationValue('debug', false),
            'trace' => $application->getConfigurationValue('debug', false),
            'ConsentStoreValues' => $this->_getConsentConfigurationValue('storeValues', true),
            'NoSupportedIDPError' => 'user',
        ));

        $attributes = array();
        require ENGINEBLOCK_FOLDER_APPLICATION . 'configs/attributes.inc.php';
        $proxyServer->setAttributeMetadata($attributes);

        $proxyServer->setHostedEntities(array(
            $proxyServer->getHostedEntityUrl($this->_hostedEntity) => array(
                'certificates' => array(
                    'public'    => $application->getConfiguration()->encryption->key->public,
                    'private'   => $application->getConfiguration()->encryption->key->private,
                ),
                // Note that we use an input filter because consent requires a presistent NameID
                // and we only get that after provisioning
                'infilter'  => array(new EngineBlock_Corto_Filter_Input($this), 'filter'),
                'outfilter' => array(new EngineBlock_Corto_Filter_Output($this), 'filter'),
                'Processing' => array(
                    'Consent' => array(
                        'Binding'  => 'INTERNAL',
                        'Location' => $proxyServer->getHostedEntityUrl($this->_hostedEntity, 'provideConsentService'),
                    ),
                ),
                'keepsession' => true,
                'idpMetadataValidUntilSeconds' => 86400, // This sets the time (in seconds) the entity metadata is valid.
                'WantsAssertionsSigned' => true,
            ),
        ));

        /**
         * Add ourselves as valid IdP
         */
        $engineBlockEntities = array(
            $proxyServer->getHostedEntityUrl($this->_hostedEntity, 'idPMetadataService') => array(
                'certificates' => array(
                    'public'    => $application->getConfiguration()->encryption->key->public,
                    'private'   => $application->getConfiguration()->encryption->key->private,
                ),
            )
        );
        $remoteEntities = $this->_getRemoteEntities();
        $proxyServer->setRemoteEntities($remoteEntities + $engineBlockEntities);

        $proxyServer->setTemplateSource(
            Corto_ProxyServer::TEMPLATE_SOURCE_FILESYSTEM,
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
    protected function _applyRemoteEntitiesFilters(EngineBlock_Corto_CoreProxy $proxyServer) {
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

    protected function _getSystemLog()
    {
        return $this->_getLog();
    }

    protected function _getSessionLog()
    {
        return $this->_getLog();
    }

    protected function _getLog()
    {
        return new EngineBlock_Corto_Log_Adapter();
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
        return new EngineBlock_Corto_ServiceRegistry_Adapter(
            new Janus_Client_CacheProxy()
        );
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
    
    protected function _getHostedEntity()
    {
        return $this->_hostedEntity;
    }

    protected function _addRemoteEntitiesFilter($callback)
    {
        $this->_remoteEntitiesFilter[] = $callback;
        return $this;
    }

    protected function _getCoreProxy()
    {
        return new EngineBlock_Corto_CoreProxy();
    }
}