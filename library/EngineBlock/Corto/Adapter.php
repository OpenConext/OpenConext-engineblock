<?php

define('ENGINEBLOCK_FOLDER_LIBRARY_CORTO', ENGINEBLOCK_FOLDER_LIBRARY . 'Corto/library/');
require ENGINEBLOCK_FOLDER_LIBRARY_CORTO . 'Corto/ProxyServer.php';

spl_autoload_register(array('EngineBlock_Corto_Adapter', 'cortoAutoLoad'));

class EngineBlock_Corto_Adapter 
{
    const DEFAULT_HOSTED_ENTITY = 'main';

    /**
     * @var EngineBlock_Corto_CoreProxy
     */
    protected $_proxyServer;

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
        $cortoHostedEntity  = self::DEFAULT_HOSTED_ENTITY;
        $cortoIdPHash       = $idPProviderHash;
        return '/' . $cortoHostedEntity . ($cortoIdPHash ? '_' . $cortoIdPHash : '') . '/' . $cortoServiceName;
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

        $proxyServer->setConfigs(array(
            'debug' => $application->getConfigurationValue('debug', false),
            'trace' => $application->getConfigurationValue('debug', false),
            'ConsentDbTable'    => $application->getConfigurationValue('Consent.Db.Table'),
        ));

        $attributes = array();
        require ENGINEBLOCK_FOLDER_LIBRARY_CORTO . '../configs/attributes.inc.php';
        $proxyServer->setAttributeMetadata($attributes);

        $proxyServer->setHostedEntities(array(
            $proxyServer->getHostedEntityUrl('main') => array(
                'certificates' => array(
                    'public'    => $application->getConfigurationValue('PublicKey'),
                    'private'   => $application->getConfigurationValue('PrivateKey'),
                ),
                'infilter' => array($this, 'filterInputAttributes'),
                'Processing' => array(
                    'Consent' => array(
                        'Binding'  => 'INTERNAL',
                        'Location' => $proxyServer->getHostedEntityUrl('main', 'provideConsentService'),
                    ),
                ),
            ),
        ));

        $proxyServer->setRemoteEntities($this->_getRemoteEntities() + array(
            $proxyServer->getHostedEntityUrl('main', 'idPMetadataService') => array(
                'certificates' => array(
                    'public'    => $application->getConfigurationValue('PublicKey'),
                    'private'   => $application->getConfigurationValue('PrivateKey'),
                ),
            )
        ));

        $proxyServer->setTemplateSource(
            Corto_ProxyServer::TEMPLATE_SOURCE_FILESYSTEM,
            array('FilePath'=>ENGINEBLOCK_FOLDER_MODULES . 'Authentication/View/Proxy/')
        );

        $proxyServer->setSessionLogDefault(new Corto_Log_File('/tmp/corto_session'));
        $proxyServer->setBindingsModule(new Corto_Module_Bindings($proxyServer));
        $proxyServer->setServicesModule(new EngineBlock_Corto_Module_Services($proxyServer));
    }

    /**
     * Called by Corto whenever it receives an Assertion with attributes from an Identity Provider
     *
     * @param  $entityMetaData
     * @param  $response
     * @param  $attributes
     * @return void
     */
    public function filterInputAttributes(array $entityMetaData, array $response, array &$attributes)
    {
        $attributes = $this->_enrichAttributes($attributes);
        $attributes = $this->_provisionUser($attributes);
    }

    protected function _enrichAttributes($attributes)
    {
        $aggregatedAttributes = $this->_getAttributeAggregator(
            $this->_getAttributeProviders()
        )->getAttributes($attributes['uid'][0]);
        return array_merge_recursive($attributes, $aggregatedAttributes);
    }

    protected function _getAttributeProviders()
    {
        return array(new EngineBlock_AttributeProvider_Dummy());
    }

    protected function _getAttributeAggregator($providers)
    {
        return new EngineBlock_AttributeAggregator($providers);
    }

    protected function _provisionUser($attributes)
    {
        return $this->_getProvisioning()->provisionUser(
            $attributes,
            $this->_getAttributesHash($attributes)
        );
    }

    protected function _getAttributesHash($attributes)
    {
        return sha1(var_export($attributes, 1));
    }

    protected function _getProvisioning()
    {
        return new EngineBlock_Provisioning();
    }

    protected function _getRemoteEntities()
    {
        $serviceRegistry = new EngineBlock_Corto_ServiceRegistry_Adapter(new EngineBlock_ServiceRegistry());
        $metadata = $serviceRegistry->getRemoteMetaData();
        return $metadata;
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
}
