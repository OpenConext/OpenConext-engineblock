<?php

define('ENGINEBLOCK_FOLDER_LIBRARY_CORTO', ENGINEBLOCK_FOLDER_LIBRARY . 'Corto/library/');
require ENGINEBLOCK_FOLDER_LIBRARY_CORTO . 'Corto/ProxyServer.php';

spl_autoload_register(array('EngineBlock_Corto_Adapter', 'cortoAutoLoad'));

class EngineBlock_Exception_UserNotMember extends EngineBlock_Exception
{
}

class EngineBlock_Exception_InvalidConnection extends EngineBlock_Exception
{

}

class EngineBlock_Exception_ReceivedErrorStatusCode extends EngineBlock_Exception
{

}

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
    protected $_remoteEntitiesFilter = NULL;
    
    public function __construct($hostedEntity = NULL) {
        
        if ($hostedEntity == NULL) {
            $hostedEntity = self::DEFAULT_HOSTED_ENTITY;
        }
        
        $this->_hostedEntity = $hostedEntity;
        
    }

    /**
     * Simple autoloader for Corto, tries to autoload all classes with Corto_ from the Corto/library folder.
     *
     * @static
     * @param string $className Class name to autoload
     * @return bool Whether autoloading succeeded
     */
    public static function cortoAutoLoad($className)
    {
        if (strpos($className, 'Corto_') !== 0) {
            return false;
        }

        $classParts = explode('_', $className);
        $filePath = implode('/', $classParts) . '.php';

        include ENGINEBLOCK_FOLDER_LIBRARY_CORTO . $filePath;

        return true;
    }

    public function singleSignOn($idPProviderHash)
    {
        $this->_setRemoteEntitiesFilter(array($this, '_filterRemoteEntitiesByRequestSp'));
        $this->_callCortoServiceUri('singleSignOnService', $idPProviderHash);
    }

    protected function _filterRemoteEntitiesByRequestSp(array $entities, EngineBlock_Corto_CoreProxy $proxyServer)
    {
        /**
         * Use the binding module to get the request, then
         * store it in _REQUEST so Corto will think it has received it
         * from an internal binding, because if Corto would try to
         * get the request again from the binding module, it would fail.
         */
        $request = $_REQUEST['SAMLRequest'] = $proxyServer->getBindingsModule()->receiveRequest();
        $spEntityId = $request['saml:Issuer']['__v'];
        return $this->_getServiceRegistryAdapter()->filterEntitiesBySp(
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

        return $this->_getServiceRegistryAdapter()->filterEntitiesBySp(
            $entities,
            $claimedSpEntityId
        );
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
        $this->_setRemoteEntitiesFilter(array($this, '_filterRemoteEntitiesBySpQueryParam'));
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
    
    public function setVirtualOrganisationContext($virtualOrganisation)
    {
        $this->_voContext = $virtualOrganisation;
    }

    protected function _callCortoServiceUri($serviceName, $idPProviderHash = "")
    {
        $cortoUri = $this->_getCortoUri($serviceName, $idPProviderHash);

        $this->_initProxy();

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
            return true;
        }

        $proxyServer = $this->_getCoreProxy();

        $this->_configureProxyServer($proxyServer);

        $this->_proxyServer = $proxyServer;
    }

    protected function _getCoreProxy()
    {
        return new EngineBlock_Corto_CoreProxy();
    }

    protected function _configureProxyServer(Corto_ProxyServer $proxyServer)
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();
        
        if ($this->_voContext!=null) {
            $proxyServer->setVirtualOrganisationContext($this->_voContext);
        }

        $proxyServer->setConfigs(array(
            'debug' => $application->getConfigurationValue('debug', false),
            'trace' => $application->getConfigurationValue('debug', false),
            'ConsentStoreValues' => $this->_getConsentConfigurationValue('storeValues', true),
            'NoSupportedIDPError' => 'user',
        ));

        $attributes = array();
        require ENGINEBLOCK_FOLDER_LIBRARY_CORTO . '../configs/attributes.inc.php';
        $proxyServer->setAttributeMetadata($attributes);

        $proxyServer->setHostedEntities(array(
            $proxyServer->getHostedEntityUrl($this->_hostedEntity) => array(
                'certificates' => array(
                    'public'    => $application->getConfiguration()->encryption->key->public,
                    'private'   => $application->getConfiguration()->encryption->key->private,
                ),
                // Note that we use an input filter because consent requires a presistent NameID
                // and we only get that after provisioning
                'infilter'  => array($this, 'filterInputAttributes'),
                'outfilter' => array($this, 'filterOutputAttributes'),
                'Processing' => array(
                    'Consent' => array(
                        'Binding'  => 'INTERNAL',
                        'Location' => $proxyServer->getHostedEntityUrl($this->_hostedEntity, 'provideConsentService'),
                    ),
                ),
                'keepsession' => false,
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

        $proxyServer->setSessionLogDefault($this->_getCortoMemcacheLog());
        
        $proxyServer->setBindingsModule(new EngineBlock_Corto_Module_Bindings($proxyServer));
        $proxyServer->setServicesModule(new EngineBlock_Corto_Module_Services($proxyServer));

        if ($this->_remoteEntitiesFilter) {
            $proxyServer->setRemoteEntities(call_user_func_array(
                $this->_remoteEntitiesFilter,
                array(
                    $proxyServer->getRemoteEntities(),
                    $proxyServer
                )
            ));
        }
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

    /**
     * Called by Corto whenever it receives an Assertion with attributes from an Identity Provider.
     *
     * Note we have to do everything that relies on the actual idpEntityMetadata here, because in the
     * filterOutputAttributes the idp metadata points to us (Corto / EngineBlock) not the actual idp we received
     * the response from.
     *
     * @param  $entityMetaData
     * @param  $response
     * @param  $responseAttributes
     * @return void
     */
    public function filterInputAttributes(&$response, &$responseAttributes, $request, $spEntityMetadata, $idpEntityMetadata)
    {
        if ($response['samlp:Status']['samlp:StatusCode']['_Value']!=='urn:oasis:names:tc:SAML:2.0:status:Success') {
            // Idp returned an error
            throw new EngineBlock_Exception_ReceivedErrorStatusCode(
                'Response received with Status: ' .
                $response['samlp:Status']['samlp:StatusCode']['_Value'] .
                ' - ' .
                $response['samlp:Status']['samlp:StatusMessage']['__v']
            );
        }

        // validate if the IDP sending this response is allowed to connect to the SP that made the request.
        $this->validateSpIdpConnection($spEntityMetadata["EntityId"], $idpEntityMetadata["EntityId"]);
        
        // Determine a Virtual Organization context
        $vo = NULL;
        
        // In filter stage we need to take a look at the VO context      
        if (isset($request['__'][EngineBlock_Corto_CoreProxy::VO_CONTEXT_KEY])) {
            $vo = $request['__'][EngineBlock_Corto_CoreProxy::VO_CONTEXT_KEY];
            $this->setVirtualOrganisationContext($vo);            
        }
        
        // Provisioning of the user account
        $subjectId = $this->_provisionUser($responseAttributes, $idpEntityMetadata);
        $_SESSION['subjectId'] = $subjectId;
        
        // If in VO context, validate the user's membership
        if (!is_null($vo)) {
            if (!$this->_validateVOMembership($subjectId, $vo)) {
                throw new EngineBlock_Exception_UserNotMember("User not a member of VO $vo");          
            }
        }

        $this->_trackLogin($spEntityMetadata['EntityId'], $idpEntityMetadata['EntityId'], $subjectId);
    }

    /**
     * Called by Corto just as it prepares to send the response to the SP
     *
     * Note that we HAVE to do response fiddling here because the filterInputAttributes only operates on the 'original'
     * response we got from the Idp, after that a NEW response gets created.
     * The filterOutputAttributes actually operates on this NEW response, destined for the SP.
     *
     * @param  $response
     * @param  $responseAttributes
     * @return void
     */
    public function filterOutputAttributes(&$response, &$responseAttributes)
    {
        $subjectId = $_SESSION['subjectId'];

        // Attribute Aggregation
        $responseAttributes = $this->_enrichAttributes($subjectId, $responseAttributes);

        // Adjust the NameID, set the collab:person uid
        $response['saml:Assertion']['saml:Subject']['saml:NameID'] = array(
            '_Format'          => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
            '__v'              => $subjectId
        );

        // Attribute manipulation / mangling
        $responseAttributes = $this->_manipulateAttributes(
            $subjectId,
            $responseAttributes,
            $response
        );
    }
    
    protected function _trackLogin($spEntityId, $idpEntityId, $subjectId)
    {
        $tracker = new EngineBlock_Tracker();
        $tracker->trackLogin($spEntityId, $idpEntityId, $subjectId);
    }
    
    protected function _validateVOMembership($subjectIdentifier, $voIdentifier)
    {
        // todo: this is pure happy flow
        
        $voClient = new EngineBlock_VORegistry_Client();  
        $metadata = $voClient->getGroupProviderMetadata($voIdentifier);
        
        $client = EngineBlock_Groups_Directory::createGroupsClient($metadata["groupprovideridentifier"]);    

        if (isset($metadata["groupstem"])) {
            $client->setGroupStem($metadata["groupstem"]);
        }
        
        return $client->isMember($subjectIdentifier, $metadata["groupidentifier"]);
    }
    
    public function validateSpIdpConnection($spEntityId, $idpEntityId)
    {
        $serviceRegistryAdapter = $this->_getServiceRegistryAdapter();
        if (!$serviceRegistryAdapter->isConnectionAllowed($spEntityId, $idpEntityId)) {
            throw new EngineBlock_Exception_InvalidConnection(
                "Received a response from an IDP that is not allowed to connect to the requesting SP"
            );
        }
    }

    /**
     * Enrich the attributes with attributes
     *
     * @param  $attributes
     * @return array
     */
    protected function _enrichAttributes($subjectId, array $attributes)
    {
        $aggregator = $this->_getAttributeAggregator(
            $this->_getAttributeProviders()
        );
        $aggregatedAttributes = $aggregator->getAttributes(
            $subjectId
        );
        return array_merge_recursive($attributes, $aggregatedAttributes);
    }

    protected function _manipulateAttributes($subjectId, array $attributes, array $response)
    {
        $manipulators = $this->_getAttributeManipulators();
        foreach ($manipulators as $manipulator) {
            $attributes = $manipulator->manipulate($subjectId, $attributes, $response);
        }
        return $attributes;
    }

    protected function _provisionUser(array $attributes, $idpEntityMetadata)
    {
        return $this->_getProvisioning()->provisionUser($attributes, $idpEntityMetadata);
    }

    protected function _getRemoteEntities()
    {
        $serviceRegistry = $this->_getServiceRegistryAdapter();
        $metadata = $serviceRegistry->getRemoteMetaData();
        return $metadata;
    }

    protected function _getServiceRegistryAdapter()
    {
        return new EngineBlock_Corto_ServiceRegistry_Adapter(
            new EngineBlock_ServiceRegistry_CacheProxy()
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

    protected function _getAttributeProviders()
    {
        return array(
            new EngineBlock_AttributeProvider_Dummy(),
        );
    }

    protected function _getAttributeManipulators()
    {
        return array(
            new EngineBlock_AttributeManipulator_File()
        );
    }

    protected function _getAttributeAggregator($providers)
    {
        return new EngineBlock_AttributeAggregator($providers);
    }

    protected function _getProvisioning()
    {
        return new EngineBlock_Provisioning();
    }

    protected function _getCortoMemcacheLog()
    {
        return new Corto_Log_Memcache($this->_getMemcacheClient());
    }

    protected function _getMemcacheClient()
    {
        $factory = new EngineBlock_Memcache_ConnectionFactory();
        return $factory->create();
    }
    
    protected function _getHostedEntity()
    {
        return $this->_hostedEntity;
    }

    protected function _setRemoteEntitiesFilter($callback)
    {
        $this->_remoteEntitiesFilter = $callback;
        return $this;
    }
}
