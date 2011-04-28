<?php

define('DAY_IN_SECONDS', 86400);
ini_set('display_errors', true);
require '_includes.php';

$server = new EntityCertificateServer();
$server->serve($_GET['eid']);

class EntityCertificateServer
{
    protected $_response;

    /**
     * @var OpenSsl_Certificate
     */
    protected $_certificate;

    /**
     * @var OpenSsl_Certificate_Chain
     */
    protected $_certificateChain;

    protected $_certificateExpiryWarningDays = 30;
    
    public function __construct()
    {
        $this->_initializeResponse();
    }

    public function setCertificateExpiryWarningDays($days)
    {
        $this->_certificateExpiryWarningDays = $days;
        return $this;
    }

    protected function _initializeResponse()
    {
        $response = new stdClass();
        $response->CertificateChain = array();
        $response->Warnings = array();
        $response->Errors = array();
        $this->_response = $response;
    }
    
    public function serve($entityId)
    {
        if (!$this->_loadEntityCertificate($entityId)) {
            return $this->_sendResponse();
        }

        $this->_checkCertificateValidity();
        $this->_loadCertificateChain();
        $this->_checkChainValidity();
        return $this->_sendResponse();
    }

    protected function _loadEntityCertificate($entityId)
    {
        $janusConfig = SimpleSAML_Configuration::getConfig('module_janus.php');
        $entityController = new sspmod_serviceregistry_EntityController($janusConfig);
        $entityController->setEntity($entityId);
        $entityController->loadEntity();

        try {
            $this->_certificate = $entityController->getCertificate();
            return true;
        } catch(Janus_Exception_NoCertData $e) {
            $this->_response->Warnings[] = "No certificate data for this entity";
            return false;
        }
    }

    protected function _checkCertificateValidity()
    {
        if ($this->_certificate->getValidFromUnixTime() > time()) {
            $this->_response->Errors[] = "Entity certificate is not yet valid";
        }
        if ($this->_certificate->getValidUntilUnixTime() < time()) {
            $this->_response->Errors[] = "Entity certificate has expired";
        }

        // Check if the certificate is still valid in x days, add a warning if it is not
        $entityMetadataMinimumValidityUnixTime = time() + ($this->_certificateExpiryWarningDays * DAY_IN_SECONDS);
        if (!$this->_certificate->getValidUntilUnixTime() > $entityMetadataMinimumValidityUnixTime) {
            $this->_response->Warnings[] = "Entity certificate will expire in less than {$this->_certificateExpiryWarningDays} days";
        }
    }

    protected function _loadCertificateChain()
    {
        $this->_certificateChain = OpenSsl_Certificate_Chain_Factory::create($this->_certificate);
        $certificates = $this->_certificateChain->getCertificates();
        foreach ($certificates as $certificate) {
            $certificateSubject = $certificate->getSubject();
        
            $this->_response->CertificateChain[] = array(
                'Subject' => array(
                    'DN' => $certificate->getSubjectDn(),
                    'CN' => $certificateSubject['CN'],
                ),
                'SubjectAlternative' => array(
                    'DNS' => $certificate->getSubjectAltNames(),
                ),
                'Issuer' => array(
                    'Dn' => $certificate->getIssuerDn(),
                ),
                'NotBefore' => array(
                    'UnixTime' => $certificate->getValidFromUnixTime(),
                ),
                'NotAfter' => array(
                    'UnixTime' => $certificate->getValidUntilUnixTime(),
                ),
                'RootCa' => $certificate->isRootCa(),
                'SelfSigned' => $certificate->isSelfSigned(),
            );
        }
    }

    protected function _checkChainValidity()
    {
        $validator = new OpenSsl_Certificate_Chain_Validator($this->_certificateChain);
        $validator->setIgnoreSelfSigned(true);
        $validator->validate();

        $this->_response->Warnings = array_merge($this->_response->Warnings, $validator->getWarnings());
        $this->_response->Errors   = array_merge($this->_response->Errors,   $validator->getErrors());
    }
    
    protected function _sendResponse()
    {
        $this->_outputContentType('application/json');
        $this->_outputResponse();
    }

    protected function _outputContentType($contentType)
    {
        header("Content-Type: $contentType");
    }

    protected function _outputResponse()
    {
        echo json_encode($this->_response);
    }
}