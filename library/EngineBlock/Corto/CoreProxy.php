<?php

class EngineBlock_Corto_CoreProxy extends Corto_ProxyServer
{
    protected $_headers = array();
    protected $_output;

    protected $_serviceToControllerMapping = array(
        'singleSignOnService'   => '/authentication/idp/single-sign-on',
        'continueToIdP'         => '/authentication/idp/process-wayf',
        'assertionConsume'      => '/authentication/sp/consume-assertion',
        'continueToSP'          => '/authentication/sp/process-consent',
    );

    public function getHostedEntityUrl($entityCode, $serviceName = "", $remoteEntityId = "")
    {
        $scheme = 'http';
        if (isset($_SERVER['HTTPS'])) {
            $scheme = 'https';
        }

        $host = $_SERVER['HTTP_HOST'];

        $entityPart = $entityCode;

        $entityPart .= '_' . md5($remoteEntityId);

        if (!$serviceName) {
            return $scheme . '://' . $host . ($this->_hostedPath ? $this->_hostedPath : ''). $entityPart;
        }

        return $scheme . '://' . $host . ($this->_hostedPath ? $this->_hostedPath : '') . $entityPart . '/' . $serviceName;
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