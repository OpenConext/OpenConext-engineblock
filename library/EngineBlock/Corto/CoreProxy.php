<?php

class EngineBlock_Corto_CoreProxy extends Corto_ProxyServer
{
    protected $_headers = array();
    protected $_output;

    protected $_serviceToControllerMapping = array(
        'singleSignOnService'       => 'authentication/idp/single-sign-on',
        'continueToIdP'             => 'authentication/idp/process-wayf',
        'assertionConsumerService'  => 'authentication/sp/consume-assertion',
        'continueToSP'              => 'authentication/sp/process-consent',
        'idPMetadataService'        => 'authentication/idp/metadata',
        'sPMetadataService'         => 'authentication/sp/metadata',
        'provideConsentService'     => 'authentication/idp/provide-consent',
        'processConsentService'     => 'authentication/idp/process-consent',
        'processedAssertionConsumerService' => 'authentication/proxy/processed-assertion'
    );

    public function getParametersFromUrl($url)
    {
        $parameters = array(
            'EntityCode'        => 'main',
            'ServiceName'       => '',
            'RemoteIdPMd5Hash'  => '',
        );
        $urlPath = parse_url($url, PHP_URL_PATH); // /authentication/x/ServiceName[/remoteIdPMd5Hash]
        if ($urlPath[0] === '/') {
            $urlPath = substr($urlPath, 1);
        }

        foreach ($this->_serviceToControllerMapping as $serviceName => $controllerUri) {
            if (strstr($urlPath, $controllerUri)) {
                $urlPath = str_replace($controllerUri, $serviceName, $urlPath);
                list($parameters['ServiceName'], $parameters['RemoteIdPMd5Hash']) = explode('/', $urlPath);
                return $parameters;
            }
        }

        throw new Corto_ProxyServer_Exception("Unable to map URL '$url' to EngineBlock URL");
    }

    public function getHostedEntityUrl($entityCode, $serviceName = "", $remoteEntityId = "")
    {
        if (!isset($this->_serviceToControllerMapping[$serviceName])) {
            return parent::getHostedEntityUrl($entityCode, $serviceName, $remoteEntityId);
        }

        $scheme = 'http';
        if (isset($_SERVER['HTTPS'])) {
            $scheme = 'https';
        }

        $host = $_SERVER['HTTP_HOST'];

        $mappedUri = $this->_serviceToControllerMapping[$serviceName] . ($remoteEntityId ? '/' . md5($remoteEntityId) : '');
        return $scheme . '://' . $host . ($this->_hostedPath ? $this->_hostedPath : '') . $mappedUri;
    }

    protected function _getAttributeDataType($type, $name, $ietfLanguageTag = 'en_US')
    {
        if (isset($this->_attributes[$name][$type][$ietfLanguageTag])) {
            return $this->_attributes[$name][$type][$ietfLanguageTag];
        }

        
        var_dump("Unable to find $name, $type, $ietfLanguageTag");
        // @todo warn the system! requested a unkown UID or langauge...
    }

    public function getOutput()
    {
        return $this->_output;
    }

    public function getHeaders()
    {
        return $this->_headers;
    }

    public function sendOutput($rawOutput)
    {
        $this->_output = $rawOutput;
    }

    public function sendHeader($name, $value)
    {
        $this->_headers[$name] = $value;
    }
}