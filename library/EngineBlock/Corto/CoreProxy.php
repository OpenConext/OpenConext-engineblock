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
    );

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