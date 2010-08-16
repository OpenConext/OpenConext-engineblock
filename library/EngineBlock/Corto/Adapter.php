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
        $cortoUri = $this->_getCortoUri('singleSignOnService', $idPProviderHash);

        $this->_initProxy();

        $this->_proxyServer->serveRequest($cortoUri);

        $this->_processProxyServerResponse();

        unset($this->_proxyServer);
    }

    protected function _getCortoUri($cortoServiceName, $idPProviderHash = "")
    {
        $cortoHostedEntity  = self::DEFAULT_HOSTED_ENTITY;
        $cortoIdPHash       = $idPProviderHash;
        return '/' . $cortoHostedEntity . ($cortoIdPHash ? $cortoIdPHash : '') . '/' . $cortoServiceName;
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

    protected function _configureProxyServer($proxyServer)
    {
        $proxyServer->setConfigs(array(
            'debug' => true,
            'trace' => true,
        ));
        $proxyServer->setRemoteEntities($this->_getRemoteEntities());
        $proxyServer->setTemplateSource(
            Corto_ProxyServer::TEMPLATE_SOURCE_FILESYSTEM,
            array('FilePath'=>ENGINEBLOCK_FOLDER_LIBRARY_CORTO . '../templates/')
        );
        $proxyServer->setSessionLogDefault(new Corto_Log_File('/tmp/corto_session'));
        $proxyServer->setBindingsModule(new Corto_Module_Bindings($proxyServer));
        $proxyServer->setServicesModule(new Corto_Module_Services($proxyServer));
    }

    protected function _getRemoteEntities()
    {
        $serviceRegistry = new EngineBlock_Corto_ServiceRegistry_Adapter(new EngineBlock_ServiceRegistry());
        return $serviceRegistry->getRemoteMetaData();
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
            if ($headerName !== EngineBlock_Http_Response::HTTP_HEADER_RESPONSE_LOCATION) {
                continue;
            }

            $response->setRedirectUrl($headerValue);
        }
    }

    protected function _processProxyServerResponseBody(EngineBlock_Http_Response $response)
    {
        $proxyOutput = $this->_proxyServer->getOutput();
        $response->setBody($proxyOutput);
    }
}
